/**
 * Admin js file
 *
 * @package    Neoship
 */

(function ($) {
    'use strict';

    $(document).ready(
        function () {

            var neoshipSelectSubmit = document.getElementById("posts-filter");

            neoshipSelectSubmit.innerHTML += `
                <div id="chooseStickerPosition" style="display:none; position:fixed; width: 100%; height: 100%; background: rgba(0,0,0,0.5); top: 0; left: 0;">
                    <div style="position: fixed; left: 50%; top: 50%; transform: translate(-50%,-50%); background: white; padding: 1rem;">
                        <b>Kliknutím vyberte pozíciu, od ktorej sa začnú tlačiť štítky.</b>
                        <br>
                        <br>
                        <div style="display: flex; width: 90px; flex-wrap: wrap; border: 1px solid #cecece;">
                            <button class="chooseStickerPositionButton" data-position="1" style="width: calc(50% - 10px); padding: 0.7rem 0; margin: 5px; display: block; text-align: center; background: #ec4f30; color:white; cursor: pointer;">1</button>
                            <button class="chooseStickerPositionButton" data-position="2" style="width: calc(50% - 10px); padding: 0.7rem 0; margin: 5px; display: block; text-align: center; background: #ec4f30; color:white; cursor: pointer;">2</button>
                            <button class="chooseStickerPositionButton" data-position="3" style="width: calc(50% - 10px); padding: 0.7rem 0; margin: 5px; display: block; text-align: center; background: #ec4f30; color:white; cursor: pointer;">3</button>
                            <button class="chooseStickerPositionButton" data-position="4" style="width: calc(50% - 10px); padding: 0.7rem 0; margin: 5px; display: block; text-align: center; background: #ec4f30; color:white; cursor: pointer;">4</button>
                        </div>
                    </div>
                </div>
            `;

            var modal = document.getElementById("chooseStickerPosition");

            modal.addEventListener('click', function(event){
                if ( event.target.id === 'chooseStickerPosition' ) {
                    modal.style.display = 'none';
                }
            });

            var elements = document.getElementsByClassName("chooseStickerPositionButton");
            for (var i = 0; i < elements.length; i++) {
                elements[i].addEventListener('click', function(event){
                    event.preventDefault();

                    var menusIds = [ "bulk-action-selector-top", "bulk-action-selector-bottom" ]; 
                    for (let index = 0; index < menusIds.length; index++) {
                        var actionSelectMenu = document.getElementById( menusIds[index] );
                        
                        var option = document.createElement("option");
                        option.value = actionSelectMenu.value + '_position_' + event.target.dataset.position;
                        actionSelectMenu.appendChild(option);
    
                        var opts = actionSelectMenu.options;
                        for (var opt, j = 0; opt = opts[j]; j++) {
                            if (opt.value == option.value) {
                                actionSelectMenu.selectedIndex = j;
                                break;
                            }
                        }
                    }

                    neoshipSelectSubmit.submit();

                    for (let index = 0; index < menusIds.length; index++) {
                        var actionSelectMenu = document.getElementById( menusIds[index] );
                        actionSelectMenu.remove( actionSelectMenu.length - 1 );
                    }
                    
                    modal.style.display = 'none';
                });
            }

            neoshipSelectSubmit.addEventListener('submit', function(event) {

                var menusIds = [ "bulk-action-selector-top", "bulk-action-selector-bottom" ]; 
                for (let index = 0; index < menusIds.length; index++) {
                    var actionSelectMenu = document.getElementById( menusIds[index] );
                    if ( [ 'neoship3_print_stickers_sps', 'neoship3_print_stickers_gls', 'neoship3_print_stickers_packeta' ,
                        'neoship3_print_stickers_123', 'neoship3_print_stickers_dpd'].includes( actionSelectMenu.value ) ) {
                        event.preventDefault();
                        modal.style.display = 'block';
                    }
                }

            });
        }
    );

})(jQuery);
