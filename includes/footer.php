<?php /* Closes main-right, container and section, then loads the scripts */ ?>
            </div>
        </div>
    </section>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"></script>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/animations.js"></script>

    <script>
        $(document).ready(function () {
            $('.testimonial-slider').slick({
                infinite: true,
                slidesToShow: 2,
                slidesToScroll: 1,
                arrows: false,
                dots: false,
                autoplay: true,
                autoplaySpeed: 3000,
                responsive: [
                    {
                        /* matches the 991px breakpoint in responsive.css */
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        });
    </script>
</body>

</html>
