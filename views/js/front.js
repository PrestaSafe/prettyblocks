document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelectorAll('.prettyblocks-tns').length > 0) {
        var sliders = document.querySelectorAll('.prettyblocks-tns');
        sliders.forEach(function (slider) {
            tns({
                container: slider,
                items: 1,
                slideBy: 'page',
                autoWidth: true,
                center: true,
                // autoHeight: true,
                autoplay: true,
                nav: false, // disable dots
                controls: false, // disable controls
                autoplayButtonOutput: false,
                mouseDrag: true,
                loop: true,
                rewind: true,
                autoplayTimeout: 4000,
            });
        });
    }
    // Apply the style on page load
    applyAdaptiveStyle();
    
    // faq 
   runFaq();
});
document.addEventListener('updatePrettyBlocks', function () {
    runFaq();
});
const runFaq = () => {
    if (document.querySelectorAll('.prettyblocks-faq').length > 0) {
        const items = document.querySelectorAll(".prettyblocks-faq .accordion button");

        function toggleAccordion() {
            const itemToggle = this.getAttribute('aria-expanded');

            for (i = 0; i < items.length; i++) {
                items[i].setAttribute('aria-expanded', 'false');
            }

            if (itemToggle == 'false') {
                this.setAttribute('aria-expanded', 'true');
            }
        }

        items.forEach(item => item.addEventListener('click', toggleAccordion));
    }
}

const applyAdaptiveStyle = () => {
    var elements = document.querySelectorAll('[style-xl], [style-lg], [style-sm]');
    var windowWidth = window.innerWidth;

    elements.forEach(function (element) {
        var currentStyle = '';

        // Check the window width and set the style according to Tailwind CSS breakpoints
        if (windowWidth >= 1280) { // xl
            currentStyle = element.getAttribute('style-xl') || '';
        } else if (windowWidth >= 768) { // md
            currentStyle = element.getAttribute('style-lg') || '';
        } else if (windowWidth <= 640) { // sm
            currentStyle = element.getAttribute('style-sm') || '';
        } 
        

        element.setAttribute('style', currentStyle);

    });
}




let resizeTimeout;
window.onresize = () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(applyAdaptiveStyle, 200);
};
