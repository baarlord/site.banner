var siteBanner = BX.namespace('siteBanner');

siteBanner.popapAjaxManager = function () {
    var request = BX.ajax.runAction('site:banner.api.ajax.apply').then(function (response) {
        var popupOptions = response.data;

        var popupButtons = false;
        if (popupOptions['ITEMS'][0]['LINK'] && popupOptions['ITEMS'][0]['BUTTON_NAME']) {
            var popupButtons = [
                new BX.PopupWindowButton({
                    text: popupOptions['ITEMS'][0]['BUTTON_NAME'],
                    className: "popup-window-button-accept site_banner_popup__button",
                    events: {
                        click: function () {
                            location.href = popupOptions['ITEMS'][0]['LINK'];
                        }
                    }
                })
            ];
        }
        var popupBody = new BX.PopupWindow('popupBody', null, {
            closeIcon: true,
            content: '<div class="popup__wrap">' +
                '<picture>' +
                '<source srcset="' + popupOptions['ITEMS'][0]['PC_IMAGE']['SRC'] + '" media="(min-width: 729px)">' +
                '<source srcset="' + popupOptions['ITEMS'][0]['MOBILE_IMAGE']['SRC'] + '" media="(min-width: 258px)">' +
                '<img src="' + popupOptions['ITEMS'][0]['PC_IMAGE']['SRC'] + '" alt="' + popupOptions['ITEMS'][0]['NAME'] + '"></picture>' +
                '</div>',
            overlay: {backgroundColor: 'black', opacity: '80'},
            buttons: popupButtons
        });
        popupBody.show();

    });
}