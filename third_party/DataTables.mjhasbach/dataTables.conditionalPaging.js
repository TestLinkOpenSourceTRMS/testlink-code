/**
 * @summary     ConditionalPaging
 * @description Hide paging controls when the amount of pages is <= 1
 * @version     1.0.0 - TestLink
 * @file        dataTables.conditionalPaging.js
 * @author      Matthew Hasbach (https://github.com/mjhasbach)
 * @contact     hasbach.git@gmail.com
 * @copyright   Copyright 2015 Matthew Hasbach
 *
 * @20200222    francisco.mancardi
 *              added hideShowingOneOfN
 *
 * License      MIT - http://datatables.net/license/mit
 *
 * This feature plugin for DataTables hides paging controls when the amount
 * of pages is <= 1. The controls can either appear / disappear or fade in / out
 *
 * @example
 *    $('#myTable').DataTable({
 *        conditionalPaging: true
 *    });
 *
 * @example
 *    $('#myTable').DataTable({
 *        conditionalPaging: {
 *            style: 'fade',
 *            hideShowingOneOfN: false, 
 *            speed: 500 // optional
 *        }
 *    });
 */

(function(window, document, $) {
    $(document).on('init.dt', function(e, dtSettings) {
        if ( e.namespace !== 'dt' ) {
            return;
        }
        var options = dtSettings.oInit.conditionalPaging || $.fn.dataTable.defaults.conditionalPaging;

        if ($.isPlainObject(options) || options === true) {
            var config = $.isPlainObject(options) ? options : {},
                api = new $.fn.dataTable.Api(dtSettings),
                speed = 'slow',
                conditionalPaging = function(e) {
                    var $paging = $(api.table().container()).find('div.dataTables_paginate'),
                        pages = api.page.info().pages;
                    
                    var $showingOneOfN;
                    if (config.hideShowingOneOfN) {
                      $showingOneOfN =  
                        $(api.table().container()).find('div.dataTables_info'); 
                    }

                    if (e instanceof $.Event) {
                        if (pages <= 1) {
                            if (config.style === 'fade') {
                                $paging.stop().fadeTo(speed, 0);
                            }
                            else {
                                $paging.css('visibility', 'hidden');
                            }

                            if (typeof $showingOneOfN != 'undefined') {
                                if (config.style === 'fade') {
                                    $showingOneOfN.stop().fadeTo(speed, 0);
                                }
                                else {
                                    $showingOneOfN.css('visibility', 'hidden');
                                }
                            }
                        }
                        else {
                            if (config.style === 'fade') {
                                $paging.stop().fadeTo(speed, 1);
                            }
                            else {
                                $paging.css('visibility', '');
                            }

                            if (typeof $showingOneOfN != 'undefined') {
                                if (config.style === 'fade') {
                                    $showingOneOfN.stop().fadeTo(speed, 1);
                                }
                                else {
                                    $showingOneOfN.css('visibility', '');
                                }
                            }
                        }
                    }
                    else if (pages <= 1) {
                        if (config.style === 'fade') {
                            $paging.css('opacity', 0);
                        }
                        else {
                            $paging.css('visibility', 'hidden');
                        }
                        if (typeof $showingOneOfN != 'undefined') {
                            if (config.style === 'fade') {
                                $showingOneOfN.css('opacity', 0);
                            }
                            else {
                                $showingOneOfN.css('visibility', 'hidden');
                            }
                        }
                    }
                };

            if ( config.speed !== undefined ) {
                speed = config.speed;
            }

            conditionalPaging();

            api.on('draw.dt', conditionalPaging);
        }
    });
})(window, document, jQuery);