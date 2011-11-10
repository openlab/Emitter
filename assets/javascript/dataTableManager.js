/**
 * File:				dataTableManager.js
 * Version:				0.0.1
 * Description:			Assists in managing one or more jQuery Data Tables at once.
 * Author:				Aaron McGowan ( www.amcgowan.ca )
 * Created:				04/11/2010
 * Language:			JavaScript
 * Contact:				me@amcgowan.ca
 */

/** Test if jQuery and jQuery's dataTable plugin exists - if not, alert
	the user. Note: They must exist for DataTableManager to operate correctly and
	also appear at run time */
if( !jQuery || !jQuery.fn.dataTableExt ) {
	alert("This class requires jQuery and jQuery's dataTable plugin");
} else {

/**
 * DataTableManager
 * 
 * Constructor for data table manager class.
 *
 * @access: public
 * @param:  string			Contains the string selector for selecting the jQuery Data table elements.
 * @param:	object			Contains an object of settings which will be used to initialize the jQuery Data Table.
 * @param: 	array			Contains an array of names used to identify data tables on a page. Names appear in order of tables.
 * @return: void
 */
var DataTableManager = function(dataTableSelector, settings, names) {
	this._init(dataTableSelector, settings, names);
};

/**
* DataTableManager.prototype
*
* Define member variables/properties and methods/functions for 
* an the DataTableManager class instance.
*/
DataTableManager.prototype = {
	/**
	* _tableInstanceIndexes
	*
	* An array of indexes, numeric values starting from 0 to n, in which n is one less then
	* the number of data table instances.
	*/
	_tableInstanceIndexes : [],
	
	/**
	* _tableInstanceNames
	*
	* An object of which each property is an identifying name for a table
	* with the numeric value of which index it is associated with in _tableInstanceIndexes.
	*/
	_tableInstanceNames : {},
	
	/**
	* _dataTableSelector
	*
	* The selector string in which was used to initialize the data tables.
	*/
	_dataTableSelector : null,

	/**
	* GetCurrentTableIndex
	*
	* @access: public
	* @param:  void
	* @return: int
	*/
	GetCurrentTableIndex : function() {
		return jQuery.fn.dataTableExt.iApiIndex;
	},
	
	/**
	* GetDataTable
	*
	* @access: public
	* @param:  mixed		Contains the identifier for a table to set active, and return an instance of.
	*						This can either be the string (name) representation and or the numeric index.
	* @return: mixed		Returns null on failure, else returns the data table instance.
	*/
	GetDataTable : function(key) {
		/* If key is undefined and or null, return the current data table instance */
		if( undefined == key || null == key ) {
			return jQuery(this._dataTableSelector).dataTable();
		}
		
		/* Test if the key is being used as the string represetation */
		if( undefined != this._tableInstanceNames[key] && undefined != this._tableInstanceIndexes[this._tableInstanceNames[key]] ) {
			jQuery.fn.dataTableExt.iApiIndex = this._tableInstanceIndexes[this._tableInstanceNames[key]];
		}
		/* Test if the key is simply the numeric index */
		else if( undefined != this._tableInstanceIndexes[key] ) {
			jQuery.fn.dataTableExt.iApiIndex = this._tableInstanceIndexes[key];
		}
		else {
			return null;
		}
		
		return jQuery(this._dataTableSelector).dataTable();
	},
	
	/**
	* _init
	*
	* Initializing method.
	*
	* @access: private
	* @param:  string			Contains the string selector for selecting the jQuery Data table elements.
	* @param:	object			Contains an object of settings which will be used to initialize the jQuery Data Table.
	* @param: 	array			Contains an array of names used to identify data tables on a page. Names appear in order of tables.
	* @return: void
	*/
	_init : function(dataTableSelector, settings, names) {
		/* set the selector */
		this._dataTableSelector = dataTableSelector;
		
		/* initialize the data table */
		var dTable = jQuery(dataTableSelector).dataTable(settings);
		
		/* build the instance indexes array, and assign name associations if necessary */
		for( var i = 0; i < dTable.length; ++i ) {
			this._tableInstanceIndexes[i] = i;
			if( names[i] ) {
				this._tableInstanceNames[names[i]] = i;
			}
		}
	}
}; /* end DataTableManager.prototype */

} /* end 'else' in if( !jQuery || !jQuery.fn.dataTableExt ) { */