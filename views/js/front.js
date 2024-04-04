document.addEventListener('DOMContentLoaded', function() {
    if (document.body.innerHTML.includes('.prettyblocks-tns')) {
   
        tns({
            container: '.prettyblocks-tns',
            items: 1,
            slideBy: 'page',
            autoplay: true,
            nav: false, // disable dots
            controls: false, // disable controls
            autoplayButtonOutput: false
        }); 
    }
});