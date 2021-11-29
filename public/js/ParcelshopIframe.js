const ParcelshopIframe = {};
ParcelshopIframe.Viewport = {
    element: null,
    originalValue: null,
    set: function () {
        if (!ParcelshopIframe.Viewport.element) {
            ParcelshopIframe.Viewport.element = document.querySelector("meta[name=viewport]");
            if (ParcelshopIframe.Viewport.element) {
                ParcelshopIframe.Viewport.originalValue = ParcelshopIframe.Viewport.element.getAttribute("content");
            }
            else {
                ParcelshopIframe.Viewport.originalValue = 'user-scalable=yes';
                ParcelshopIframe.Viewport.element = document.createElement('meta');
                ParcelshopIframe.Viewport.element.setAttribute("name", "viewport");
                (document.head || document.getElementsByTagName('head')[0]).appendChild(ParcelshopIframe.Viewport.element);
            }
        }
        ParcelshopIframe.Viewport.element.setAttribute('content', 'width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=yes');
    },
    restore: function () {
        if (ParcelshopIframe.Viewport.originalValue !== null) {
            ParcelshopIframe.Viewport.element.setAttribute('content', ParcelshopIframe.Viewport.originalValue);
        }
    }
};
ParcelshopIframe.Widget = {
    baseUrl: 'https://parcelshops.neoship.sk/',
    close: function () { },
    open: function (callback, options = {}) {
        ParcelshopIframe.Widget.close();

        ParcelshopIframe.Viewport.set();
        let wrapper = document.createElement("div");
        wrapper.setAttribute("style", "z-index: 999999; position: fixed; -webkit-backface-visibility: hidden; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.3); ");
        wrapper.addEventListener("click", function () {
            ParcelshopIframe.Widget.close();
        });

        setTimeout(
            function () {
                var rect = iframe.getBoundingClientRect();
                var width = ('width' in rect ? rect.width : rect.right - rect.left);
                if (Math.round(width) < window.innerWidth - 10) { // 10px = side padding sum, just as a safety measure
                    iframe.style.width = window.innerWidth + "px";
                    iframe.style.height = window.innerHeight + "px";
                }
            },
            0
        );

        wrapper.addEventListener("keyup", function (e) {
            if (e.keyCode === 27) {
                ParcelshopIframe.Widget.close();
            }
        });
        var iframe = document.createElement("iframe");

        iframe.setAttribute("style", "border: hidden; position: absolute; left: 0; top: 0; width: 100%; height: 100%; padding: 10px 5px; box-sizing: border-box; ");
        iframe.setAttribute('id', "packeta-widget");
        iframe.setAttribute('sandbox', "allow-scripts allow-same-origin");
        iframe.setAttribute('allow', "geolocation");
        iframe.setAttribute('src', ParcelshopIframe.Widget.baseUrl + '?' + (new URLSearchParams(options)).toString());

        wrapper.appendChild(iframe);
        document.body.appendChild(wrapper);

        if (wrapper.getAttribute("tabindex") === null) {
            wrapper.setAttribute("tabindex", "-1"); // make it focusable
        }
        wrapper.focus();

        var receiver = function (e) {
            if (e.data === 'close') {
                ParcelshopIframe.Widget.close();
                return;
            }
            if (e.data.id) {
                ParcelshopIframe.Widget.close(e.data);
            }
        };
        window.addEventListener('message', receiver);

        ParcelshopIframe.Widget.close = function (point) {
            window.removeEventListener('message', receiver);
            document.body.removeChild(wrapper);
            ParcelshopIframe.Viewport.restore();
            callback(point || null);
            ParcelshopIframe.Widget.close = function () { };
        };
    }
};

// export default ParcelshopIframe;