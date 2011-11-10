<?php defined('BASEPATH') OR exit; ?>
        <div class="messages display-none" id="messages"></div>
        <ul class="search-box-tabs" id="search-box-tabs">
            <li title="nearby" class="first active" id="search-by-nearby"><a>Nearby</a></li>
            <li title="city" id="search-by-city"><a>City</a></li>
            <li title="riding" class="last" id="search-by-riding"><a>Riding</a></li>
        </ul>
        <div class="clear-fix">&nbsp;</div>
        <div class="search-box" id="search-box">
            <ul class="form" id="search-form">
                <li class="left">
                    <label for="search-terms">
                        <input type="text" name="address-field" id="search-terms" size="45" maxlength="255" class="text" />
                    </label>
                </li>
                <li id="proximity-field">
                    <label for="proximity">Proximity:</label>
                    <select name="proximity" id="proximity">
                        <?php /* <option value="all">All</option> */ ?>
                        <option value="5" selected="selected">5 KM</option>
                        <option value="10">10 KM</option>
                        <option value="25">25 KM</option>
                    </select>
                </li>
                <li class="right"><input type="button" name="search" value="Search" id="search" class="button" /></li>
            </ul>
            <input type="hidden" name="search-by" id="search-by" value="" />
            <div class="clear-fix">&nbsp;</div>
        </div>
        <div class="howto content-box" id="how-to">
            <ul class="columns">
                <li class="left">
                    <h2>Searching Nearby</h2>
                    <p>Click the <strong>NEARBY</strong> tab and enter a Canadian address to search for pollution info on nearby facilities.</p>
					<p>You can narrow down or broaden your geographic search by specifying a proximity to an address (5KM, 25KM, etc.) If everything goes well (fingers crossed), you should get back a search result with all facilities in the range you specified, provided those facilities reported into the <a href="http://www.ec.gc.ca/inrp-npri/default.asp" title="National Pollutant Release Inventory">National Pollutant Release Inventory</a>*.</p>
					<p>Note that you can use a full address, street name with a city and province, just a city, or just a postal code, and we try to figure out what you are looking for and provide some choices if your search is too ambiguous.</p>
                </li>
                <li class="middle">
                    <h2>Searching for a City</h2>
                    <p>Click the <strong>CITY</strong> tab and enter an address to search for polluting facilities within a city boundary.</p>
					<p>You can enter a full address, city and province or just a postal code and we'll look up any facilities in that city that reported pollution data into the NPRI*. This will give you an idea of how many facilities in your city are polluting.</p>
					<p>Note that this search will show only those facilities with a city address that matched the city boundaries of the address you specify. If your address is close to the city boundaries, or if you get back a large number of search results, use the NEARBY search instead.</p>
                </li>
                <li class="right">
                    <h2>Searching within a Riding</h2>
                    <p>Click the <strong>RIDING</strong> tab and enter an address to search for polluting facilities within the same riding.</p>
					<p>You can enter a postal code or a full address and we'll look up the Federal Electoral Riding and Member of Parliament information for that location, compliments of our friends at <a href="http://www.howdtheyvote.ca/" title="How'd They Vote">howdtheyvote.ca</a>. We then go and find all facilities that reported pollution data into the NPRI within the boundaries of that riding.</p>
					<p>This gives you have a way to not only see polluting facilities in your riding, but also to easily find which MP to contact. Hint: click on a facility on a map (or in the search results table) to find the MP contact information.</p>
                </li>
            </ul>
            <div class="clear-fix">&nbsp;</div>
            <p><em>*Currently Emitter only shows the <a href="http://www.ec.gc.ca/inrp-npri/default.asp?lang=En&n=B85A1846-1" title="air quality data release by NPRI in 2008">air quality data released</a> by NPRI in 2008, but we're adding new features and look for your suggestions &amp; feedback.</em></p>
        </div>
        <div class="results content-box" id="results">
			<div id="search-info" class="display-none">
				<h2>Riding: <span class="val"></span></h2>
			</div>
			
            <div id="bing-maps" class="bing-maps" style="position: relative; width: 938px; height: 350px;"></div>
            
            <h2 class="search-results-total-heading"><span id="num-results">0</span> Results found while Searching</h2>
            <div class="information">
                <table class="facilities _datatable" style="width: 100%" width="100%">
                <thead>
					<tr>
						<th class="sorting" style="width: 5%;">ID</th>
						<th class="sorting_asc" style="width: 65%;">Facility (sector)</th>
						<th class="sorting" style="width: 15%;">Distance</th>
						<th class="sorting" style="width: 15%;">% Ranking</th>
					</tr>
                </thead>
                <tbody>
					<!-- void -->
                </tbody>
                </table>
            </div>
        </div>
        <div class="facility-details-modal modal" id="facility-details-modal">
			<div class="modal-inner">
				<div class="modal-tabs">
				<ul>
					<li title="facility-overview" class="active"><a>Facility Overview</a></li>
					<li title="released-chemicals" class="last"><a>Released Chemicals</a></li>
				</ul>
				<div class="clear-fix">&nbsp;</div>
				</div>
				<div id="facility-overview" class="tab-content display-none">
					<h2 id="facility-name">Facility Name</h2>
					<div class="facility-quick-info">
						<div class="facdeets-left">
							<ul class="facility-deets display-none">
								<li id="company"><div class="lbl">Company:</div><span class="val"></span></li>
								<li id="sector"><div class="lbl">Sector:</div><span class="val"></span></li>
								<li class="spacer">&nbsp;</li>
								<li id="location"><div class="lbl">Location:</div><span class="val"></span></li>
								<li id="geolocation"><div class="lbl">Geolocation:</div><span class="val"></span></li>
								<li class="spacer">&nbsp;</li>
								<li id="website" class="display-none"><div class="lbl">Website:</div><span class="val"></span></li>
								<li id="npri-website"><div class="lbl">Environment Canada Website:</div><span class="val"></span></li>
							</ul>
							&nbsp;
							<ul class="facility-contacts display-none">
							</ul>
						</div>
						<div class="facdeets-right">
							<div class="quick-charts">
								<ul>
									<li id="percent_rank">
										<h3>Percent Ranking</h3>
										<div class="heat-bar">
											<div class="heat-bar-marker" id="heat-bar-marker">&nbsp;</div>
											<div class="heat-bar-percent" id="heat-bar-percent"></div>
										</div>
									</li>
								</ul>
							</div>
							
							&nbsp;
							<ul class="facility-deets display-none" id="fed-riding-mp-info">
								<li><h3>Federal Riding &amp; MP Information</h3></li>
								<li id="mp-name"><span class="val"></span></li>
								<li id="mp-phone"><span class="lbl">Phone:</span><span class="val"></span></li>
								<li id="mp-email"><span class="lbl">Email:</span><span class="val"></span></li>
								<li id="mp-www"><span class="lbl">Website:</span><span class="val"></span></li>
								<li id="mp-gc-www"><span class="lbl">Official Website:</span><span class="val"></span></li>
								<li class="spacer">&nbsp;</li>
								<li id="fed-riding-name"><div class="lbl">Riding Name:</div><span class="val"></span></li>
							</ul>
						</div>
						<div class="clear-fix">&nbsp;</div>
						<p><em>Facility data last update: 2008.</em></p>
					</div>
				</div>
				<div id="released-chemicals" class="tab-content display-none">
					<table class="facility-subrelease _datatable">
					<thead>
						<tr>
							<th style="width: 5%;">CAS Number</th>
							<th style="width: 65%;">Chemical Name</th>
							<th style="width: 15%;">Adj. Air</th>
							<th style="width: 15%;">% Ranking</th>
						</tr>
					</thead>
					<tbody>
						<!-- void -->
					</tbody>
					</table>
					<div class="clear-fix">&nbsp;</div>
				</div>
			</div>
			<div class="modal-footer">
				<p><a href="#" class="jqmClose">Close</a></p>
			</div>
        </div>