    VEShape.prototype._whopollutes_id = null;
    VEShape.prototype.getWhoPollutesID = function() {
		return this._whopollutes_id;
    };
    VEShape.prototype.setWhoPollutesID = function(id) {
		this._whopollutes_id = id;
    };
    
    VEShape.prototype._whopollutes_is_facility = false;
    VEShape.prototype.setWhoPollutesIsFacility = function(is) {
		this._whopollutes_is_facility = is;
    };
    VEShape.prototype.getWhoPollutesIsFacility = function() {
		return this._whopollutes_is_facility;
    };
    
    var EmissionsWebApp = function(map_instance) {
        this._do_user_geolocation_lookup();
        
        this._map = map_instance;
        this._map.LoadMap();
        this._map.SetMapMode(VEMapMode.Mode2D);
        
        this._user_pushpin_veshape = new VECustomIconSpecification();
        this._user_pushpin_veshape.Image = AppConfig.assets_url + 'images/pin-point-red.png';
        
        this._facility_pushpin_veshape = new VECustomIconSpecification();
        this._facility_pushpin_veshape.Image = AppConfig.assets_url + 'images/icon-marker-stacks.png';
        
    };
    
    EmissionsWebApp.prototype = {
        _user_geolocation : { lat: null, lng : null, last_lookup : null, has_lookedup : false },
        _map : null,
        _user_pushpin_veshape : null,
        _facility_pushpin_veshape : null,
        
        _loaded_results_pushpin_ids : [],
        
        _dataTableManager : null,
        
        _resultsDataTableSettings : {
			"bLengthChange": false,
			"sPaginationType": "full_numbers",
			"iDisplayLength": 5
        },
        
        _resultsDataTable : function() {
			return this._dataTableManager.GetDataTable("results");
			
			//$.fn.dataTableExt.iApiIndex = 0;
			//return $("._datatable").dataTable();
        },
        
        _facChemRelDataTableSettings : {
			"bLengthChange": false,
			"sPaginationType": "full_numbers",
			"iDisplayLength": 7
        },
        
        _facChemRelDataTable : function() {
			return this._dataTableManager.GetDataTable("chemicals");
			// $.fn.dataTableExt.iApiIndex = 1;
			// return $("._datatable").dataTable();
        },
        
        
        
        init : function() {
			$("#results").addClass('display-none'); /* When adding this inline, bing maps has similar problem to NextStop's map... */
            
            this._dataTableManager = new DataTableManager('._datatable', this._resultsDataTableSettings, ["results", "chemicals"]);
            
            // $("._datatable").dataTable(this._resultsDataTableSettings);
            
           // $("#results .information .facilities").dataTable(this._resultsDataTableSettings);
           // $("#facility-details-modal #chemicals-released .facility-subrelease").dataTable(this._facChemRelDataTableSettings);
            
            this._set_map_center_user_geolocation();
            
            $("#search-box #search").bind('click', function(ref) {
                return function() { ref._search($("#search-box #search-terms").val(), $("#search-box #proximity").val(), $("#search-box #search-by").val()); };
            }(this));
            
            $("#search-box #search-terms").bind('keypress', function(event) {
				if( event.which == 13 ) {
					$("#search-box #search").trigger('click');
				}
			});
            
            $("#search-box-tabs li a").bind('click', function() {
                $("#search-box-tabs li").each(function() {
                    if( $(this).hasClass('active') )
                        $(this).removeClass('active');
                });
                
                $(this).parent().addClass('active');
                $("#search-by").attr('value', $(this).parent().attr('title'));
                
                var title = $(this).parent().attr('title');
                if( title == 'riding' || title == 'city' ) {
                    $("#search-form #proximity-field").addClass('display-none');
                } else {
                    $("#search-form #proximity-field").removeClass('display-none');
                }
            });
            $("#search-by").attr('value', $("#search-box-tabs li.active").attr('title'));
            
            $("#facility-details-modal .modal-tabs li a").bind('click', function() {
				if( $(this).parent().hasClass('active') ) {
					return;
				}
				
				$("#facility-details-modal .modal-tabs li").each(function() {
					$(this).removeClass('active');
					$("#facility-details-modal #" + $(this).attr('title')).addClass('display-none');
				});
				
				$(this).parent().addClass('active');
				$("#facility-details-modal #" + $(this).parent().attr('title')).removeClass('display-none');
            });
            
            var ref = this;
            this._map.AttachEvent("onclick", function(e) {
				if( "onclick" == e.eventName ) {
					if( e.leftMouseButton && null != e.elementID ) {
						var shape = ref._map.GetShapeByID(e.elementID);
						if( shape.getWhoPollutesIsFacility() ) {
							ref._lookup_single_facility(shape.getWhoPollutesID());
						}
					}
				}
            });
        },
        
        _search : function(terms, proximity, filter_type) {
            this._reset_messages();
            
            if( !terms ) {
                this._show_message('error', 'You must enter search criteria terms before you can perform a query.');
                return;
            }
            
            $("#loading-screen-modal").jqmShow();
            
            var ref = this;
            $.ajax({
                async : true,
                type: 'POST',
                url : AppConfig.base_url + 'api/search/',
                dataType : 'json',
                data : {
                    search_terms : terms,
                    search_proximity : proximity,
                    search_by : filter_type
                },
                success : function(data, textStatus, XMLHttpRequest) {
                    ref._clear_map_pins();
                    ref._clear_results_container();
                    
                    if( "OK" == data.status && 0 < data.results.length ) {
						$("#results").removeClass('display-none');
						ref._display_howto(false);
						
                        ref._add_user_location_to_map(data.search.location.latitude, data.search.location.longitude, "");
                        ref._map.SetCenterAndZoom(new VELatLong(data.search.location.latitude, data.search.location.longitude), 12);
                        
                        ref._handle_search_results(data.results);
                        
                        if( data.search && 'riding' == data.search.type && data.search.riding.mp ) {
							$("#search-info h2 .val").html(data.search.riding.mp.riding_name + ", <em>" + data.search.riding.mp.party + "</em>");
							$("#search-info").removeClass('display-none');
                        }
                        else {
							$("#search-info").addClass('display-none');
                        }
                        
                        $("#results-nothing").addClass('display-none');
                    }
                    else if( "PARTIAL" == data.status && 0 < data.search_ambiguous.length ) {
                        ref._build_ambiguous_results(data.search_ambiguous);
                    }
                    else {
						ref._display_howto(false);
						
						$("#results").removeClass('display-none');
                        $("#results-nothing").removeClass('display-none');
                        
                        ref._show_message("notice", "We were unable to locate any facilities and their data based on your search criteria. Please try again.");
                    }
                    $("#loading-screen-modal").jqmHide();
                },
                error : function() {
                    $("#results-nothing").removeClass("display-none");
                    $("#loading-screen-modal").jqmHide();
                }
            });
        },
        
        _lookup_single_facility : function(id) {
			this._reset_messages();
			
			this._reset_facility_details_modal();
			this._set_facility_details_modal_to_loading();
			
			var ref = this;
			$.ajax({
				async: false,
				dataType: 'json',
				url: AppConfig.base_url + 'api/facility/' + id,
				success : function(data, textStatus, XMLHttpRequest) {
					if( "OK" == data.status ) {
						$("#facility-details-modal #facility-name").html(data.result.name);
						
						$("#facility-details-modal .facility-deets #company .val").html(data.result.company_name);
						$("#facility-details-modal .facility-deets #sector .val").html(data.result.sector_name);
						
						$("#facility-details-modal .facility-deets #location .val").html(data.result.city + ", " + data.result.province);
						
						$("#facility-details-modal .facility-deets #geolocation .val").html(data.result.location.latitude + ", " + data.result.location.longitude);
						
						if( data.result.website_url ) {
							$("#facility-details-modal .facility-deets #website .val").html(data.result.website_url);
							$("#facility-details-modal .facility-deets #website").removeClass('display-none');
						}
						
						$("#facility-details-modal .facility-deets #npri-website .val").html('<a href="' + data.result.npri_website + '" title="Environment Canada Website" target="_blank">Facility & Substance Release Information</a>');
						
						if( data.result.ranking ) {
							var percentrank = Math.ceil(data.result.ranking.percent_rank);
							percentrank = percentrank - 2; // this is just to simply help with placement of marker
							$("#facility-details-modal .quick-charts #percent_rank .heat-bar-marker").css("margin-left", percentrank + "%")
							$("#facility-details-modal .quick-charts #percent_rank .heat-bar-percent").html(data.result.ranking.percent_rank + '%');
						} else {
							$("#facility-details-modal .quick-charts #percent_rank").addClass('display-none');
						}
						
						if( data.result.riding && data.result.riding.mp ) {
							$("#facility-details-modal #fed-riding-mp-info").removeClass('display-none');
							$("#facility-details-modal .facility-deets #mp-name .val").html(data.result.riding.mp.name);
							$("#facility-details-modal .facility-deets #mp-phone .val").html(data.result.riding.mp.phone);
							$("#facility-details-modal .facility-deets #mp-email .val").html('<a href="mailto:' + data.result.riding.mp.email + '" title="Send an MP">' + data.result.riding.mp.email + '</a>');
							$("#facility-details-modal .facility-deets #mp-www .val").html('<a href="' + data.result.riding.mp.website + '" title="Visit MP Website" target="_blank">' + data.result.riding.mp.website + '</a>');
							$("#facility-details-modal .facility-deets #mp-gc-www .val").html('<a href="' + data.result.riding.mp.website_official + '" title="Visit Official GoC Website" target="_blank">Visit Parliament Website</a>');
							
							$("#facility-details-modal .facility-deets #fed-riding-name .val").html(data.result.riding.mp.riding_name);
						} else {
							$("#facility-details-modal #fed-riding-mp-info").addClass('display-none');
						}
						
						if( data.result.contacts ) {
							
							var contactLi;
							for( var k in data.result.contacts ) {
								contactLi = '<li id="contact-' + k + '">';
								contactLi += '<div class="contact"><ul>';
								contactLi += '<li id="contact-name"><span class="lbl">Name:</span><span class="val">' + data.result.contacts[k].name + '</span></li>';
								contactLi += '<li id="contact-position"><span class="lbl">Position:</span><span class="val">' + data.result.contacts[k].position + '</span></li>';
								contactLi += '<li id="contact-phone"><span class="lbl">Phone Number:</span><span class="val">' + data.result.contacts[k].phone + '</span></li>';
								if( data.result.contacts[k].email ) {
									contactLi += '<li id="contact-email"><span class="lbl">Email:</span><span class="val"><a href="mailto:' + data.result.contacts[k].email + '" title="Email ' + data.result.contacts[k].name + '">' + data.result.contacts[k].email + '</a></span></li>';
								}
								contactLi += '</ul></div></li>';
								$("#facility-details-modal .facility-contacts").append(contactLi);
							}
							$("#facility-details-modal .facility-contacts").removeClass('display-none');
						}
						
						if( data.result.chemicals ) {
							ref._handle_facility_chemical_results(data.result.chemicals);
						}
						
						$("#facility-details-modal .facility-deets").removeClass('display-none');
						
						
						$("#facility-details-modal #facility-overview").removeClass('display-none');
						
						$("#facility-details-modal").jqmShow();
					} else {
						ref._show_message("error", "An error has occurred while trying to retrieve this facility's information. Please try again later.");
					}
				},
				error : function() {
					ref._show_message("error", "An error has occurred while trying to retrieve this facility's information. Please try again later.");
				}
			});
        },
        
        _reset_facility_details_modal : function() {
			$("#facility-details-modal #facility-name").html("");
			$("#facility-details-modal .facility-deets").addClass('display-none');
			$("#facility-details-modal .facility-deets #company .val").html("");
			$("#facility-details-modal .facility-deets #sector .val").html("");
			$("#facility-details-modal .facility-deets #geolocation .val").html("");
			$("#facility-details-modal .facility-deets #website .val").html("");
			$("#facility-details-modal .facility-deets #website").addClass('display-none');
			
			$("#facility-details-modal .facility-deets #location .val").html("");
			
			$("#facility-details-modal .quick-charts #percent_rank #heat-bar-marker").attr('style', "");
			$("#facility-details-modal .quick-charts #percent_rank").removeClass('display-none');
			
			$("#facility-details-modal #facility-overview").addClass('display-none');
			$("#facility-details-modal #released-chemicals").addClass('display-none');
			
			$("#facility-details-modal .modal-tabs li[title='facility-overview']").addClass('active');
			$("#facility-details-modal .modal-tabs li[title='released-chemicals']").removeClass('active');
			
			$("#facility-details-modal .facility-contacts").addClass('display-none');
			$("#facility-details-modal .facility-contacts").html('<li><h3>Facility Contacts</h3></li>');
			
			this._clear_facility_chemicals_container();
        },
        
        _set_facility_details_modal_to_loading : function() {
			$("#facility-details-modal #facility-name").html("Loading...");
			$("#facility-details-modal .facility-deets").addClass('display-none');
        },
        
        _build_ambiguous_results : function(terms) {
            this._reset_messages();
            $("#results-nothing").removeClass('display-none');
            
            if( 0 < terms.length ) {
				var list = $('<ul id="ambiguous_results" class="ambiguous_results"></ul>');
				list.append($('<li class="no-style" style="margin-left: -15px"><span style="font-size:14px; font-weight: bold;">Did you mean one of the following?</span></li>'));
				for( var k in terms ) {
					var item = $('<li><a id="ambiguous-' + k + '" title="' + terms[k].formatted_address + '">' + terms[k].formatted_address + '</a></li>');
					item.bind('click', function(ref, term) {
						return function() {
							item.unbind('click');
							ref._search(term.formatted_address, $("#search-box #proximity").val(), $("#search-box #search-by").val());
						};
					}(this, terms[k]));
					list.append(item);
				}
				
				$("#messages").html(list);
				$("#messages").addClass('notice-message');
				$("#messages").removeClass('display-none');
            }
        },
        
        _display_howto : function(on) {
            if( on )
                $("#how-to").removeClass('display-none');
            else
                $("#how-to").addClass('display-none');
        },
        
        _add_user_location_to_map : function(lat, lng, title) {
            try {
                var user_pin = new VEShape(VEShapeType.Pushpin, new VELatLong(lat, lng));
                user_pin.SetCustomIcon(this._user_pushpin_veshape);
                user_pin.Show();
                this._map.AddShape(user_pin);
            } catch( exception ) {
                // void
            }
        },
        
        _clear_map_pins : function() {
            this._map.Clear();
            this._loaded_results_pushpin_ids = [];
        },
        
        _handle_facility_chemical_results : function(chemicals) {
			if( 0 < chemicals.length ) {
				for( var k in chemicals ) {
					var rIndex = this._facChemRelDataTable().fnAddData([
						chemicals[k].cas_number,
						chemicals[k].chem_name,
						chemicals[k].adj_air,
						chemicals[k].percentrank
					]);
				}
				// this._facChemRelDataTable().fnAdjustColumnSizing();
			}
        },
        
        _handle_search_results : function(results) {
            if( 0 < results.length ) {
                var map_layer = new VEShapeLayer();
                for( var k in results ) {
					/** Commented out because this needs to be fixed. Images are way to big for the column size */
					var rankingHeatBar = '';
					if( results[k].ranking ) {
						var percentrank = Math.ceil(results[k].ranking.percent_rank);
						percentrank = percentrank - 2; // this is just to simply help with placement of marker
						
						rankingHeatBar = '<div class="heat-bar-small" style="margin: 0px 10px;" id="result-nprid-' + results[k].id + '">';
						rankingHeatBar += '<div class="heat-bar-marker" id="heat-bar-marker" style="margin-left: ' + percentrank + '%">&nbsp;</div>';
						rankingHeatBar += '<div class="heat-bar-percent">' + results[k].ranking.percent_rank + '%</div>';
						rankingHeatBar += '</div>';
						
					} else {
						rankingHeatBar = '&nbsp;';
					}
                
                    var rIndex = this._resultsDataTable().fnAddData([
						results[k].id,
						('<div class="facility-name">' + results[k].name + '</div>' + '<div class="company-name"><span>Company:</span>' + results[k].company_name + '</div>' +
							'<div class="sector"><span>Sector:</span>' + (results[k].sector_name ? results[k].sector_name : 'Unknown Sector') + '</div>'),
						results[k].location.distance,
						// (results[k].ranking ? (results[k].ranking.percent_rank + '%') : 'Unknown')
						rankingHeatBar
                    ]);
                    
                    var rNode = this._resultsDataTable().fnGetNodes(rIndex);
                    $(rNode).bind('click', function(ref, item) {
						return function() {
							ref._lookup_single_facility(item.id);
						};
                    }(this, results[k]));
                    
                    try {
                        var pin = new VEShape(VEShapeType.Pushpin, new VELatLong(results[k].location.latitude, results[k].location.longitude));
                        pin.setWhoPollutesIsFacility(true);
                        pin.setWhoPollutesID(results[k].id);
                        pin.SetCustomIcon(this._facility_pushpin_veshape);
                        pin.SetTitle("<h4>" + results[k].name + "</h4>");
                        
                        var description = '<p>' + results[k].location.address_line1;
                        if( results[k].location.address_line2 ) {
							description += " " + results[k].location.address_line2;
						}
						
						description += "<br />" + results[k].location.address_city;
						description += " " + results[k].location.address_prov;
						description += " " + results[k].location.address_postalcode;
						description += '</p>';
                        
                        description += '<p><strong>Distance:</strong> ' + results[k].location.distance + '</p>';
                        
                        description += '<p style="margin-top: 15px; font-size: 11px;">Click the icon for more information</p>';
                        
                        pin.SetDescription(description); 
                        pin.Show();
                        
                        this._loaded_results_pushpin_ids[pin.GetID()] = results[k].id;
                        
                        map_layer.AddShape(pin);
                    } catch( exception ) {
                        // alert(exception.message);
                    }
                }
                this._map.AddShapeLayer(map_layer);
                
                this._resultsDataTable().fnAdjustColumnSizing();
                
                $("#results h2 span#num-results").html(results.length);
                return true;
            } else {
                $("#results h2 span#num-results").html("0");
            }
            
            return false;
        },
        
        _clear_facility_chemicals_container : function() {
			this._facChemRelDataTable().fnClearTable();
		},
        
        _clear_results_container : function() {
            this._resultsDataTable().fnClearTable();
            $("#results h2 span#num-results").html("0");
        },
        
        _show_message : function(type, message, dont_use_para) {
            var style_class = 'notice-message';
            switch( type ) {
                case 'error':
                    style_class = 'error-message';
                    break;
                
                case 'success':
                    style_class = 'success-message';
                    break;
            }
            
            $("#messages").addClass(style_class);

            if( dont_use_para )
                $("#messages").append(message);
            else
                $("#messages").append('<p>' + message + '</p>');
            
            $("#messages").removeClass('display-none');
        },
        
        _reset_messages : function() {
            $("#messages").addClass('display-none');
            $("#messages").removeClass('error-message');
            $("#messages").removeClass('success-message');
            $("#messages").removeClass('notice-message');
            
            $("#messages").html("");
        },
        
        _do_user_geolocation_lookup : function() {
            /** commented out as we do not require this for this version of Emitter */
            /* if( navigator.geolocation ) {
                var ref = this;
                navigator.geolocation.getCurrentPosition(function(p) {
                    ref._user_geolocation.lat = pos.coords.latitude ? pos.coords.latitude : (pos.latitude ? pos.latitude : null);
                    ref._user_geolocation.lng = pos.coords.longitude ? pos.coords.longitude : (pos.longitude ? pos.longitude : null);
                    ref._user_geolocation.last_lookup = new Date();
                    ref._user_geolocation.has_lookedup = true;
                    
                    var interval = window.setInterval(function() {
                        ref._set_map_center_user_geolocation();
                        window.clearInterval(interval);
                    }, 2000);
                });
                
                return true;
            }
            */
            return false;
        },
        
        _set_map_center_user_geolocation : function() {
            if( this._user_geolocation.has_lookedup && this._user_geolocation.lat && this._user_geolocation.lng) {
                this._map.SetCenterAndZoom(new VELatLong(this._user_geolocation.lat, this._user_geolocation.lng), 10);
                return true;
            }
            
            return false;
        }
    };
    
    $(document).ready(function() {
		$("#facility-details-modal").jqm();
		$("#loading-screen-modal").jqm();
		
        var emissions_instance = new EmissionsWebApp(new VEMap("bing-maps"));
        emissions_instance.init();
    });