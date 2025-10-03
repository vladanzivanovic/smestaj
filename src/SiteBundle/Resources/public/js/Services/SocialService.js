export default (() => {
    let Public = {};

    Public.fbInit = function () {
        window.fbAsyncInit = function() {
            // init the FB JS SDK
            FB.init({
                appId      : FACEBOOK_CONFIG.app_id,
                status     : true,
                xfbml      : true,
                version    : FACEBOOK_CONFIG.graph_version
            });
        };

        // Load the SDK asynchronously
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/rs_SR/all.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    };

    Public.shareFacebook = function(e, elm){
        e.preventDefault();
        e.stopPropagation();

        var url = tjq(elm).data('url');

        FB.ui({
            method: 'share',
            mobile_iframe: true,
            href: url
        }, function(response){});
    };
    
    Public.shareGoogle = function (e, elm) {
        e.preventDefault();
        e.stopPropagation();

        window.open(tjq(elm).attr('href'), 'Google Share', 'width=500, height=400');
    }

    return Public;
})();