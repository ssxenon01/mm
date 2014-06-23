<?php

/**
 * Alternate "loop" to display posts in blog style.
 */

global $bunyad_loop;

if (!is_object($bunyad_loop)) {
    $bunyad_loop = $wp_query;
}

if ($bunyad_loop->have_posts()):

?>
<div class="city-pulse" >
    <div class="row">
        <div class="clearfix"></div>
        <div class="news-break">
            <div class="title">Сонин хачин</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="green-box">
                        <div class="thumb pull-left">
                            <div class="c-title">
                                <p class="tp">Flavor of</p>
                                <p>the Month</p>
                            </div>
                            <img src="http://localhost:8888/mymenu/resources/uploads/2014/06/fruit-yoghurt-dessert-150x150.jpg" width="200" height="160">
                        </div>
                        <div class="description"><span>Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="yellow-box">
                        <div class="thumb pull-left">
                            <div class="c-title">
                                <p class="tp">Flavor of</p>
                                <p>the Month</p>
                            </div>
                            <img src="http://localhost:8888/mymenu/resources/uploads/2014/06/fruit-yoghurt-dessert-150x150.jpg" width="200" height="160">
                        </div>
                        <div class="description"><span>Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="green-box">
                        <div class="thumb pull-left">
                            <div class="c-title">
                                <p class="tp">Flavor of</p>
                                <p>the Month</p>
                            </div>
                            <img src="http://localhost:8888/mymenu/resources/uploads/2014/06/fruit-yoghurt-dessert-150x150.jpg" width="200" height="160">
                        </div>
                        <div class="description"><span>Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="yellow-box">
                        <div class="thumb pull-left">
                            <div class="c-title">
                                <p class="tp">Flavor of</p>
                                <p>the Month</p>
                            </div>
                            <img src="http://localhost:8888/mymenu/resources/uploads/2014/06/fruit-yoghurt-dessert-150x150.jpg" width="200" height="160">
                        </div>
                        <div class="description"><span>Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio</span></div>
                    </div>
                </div>

            </div>

        </div>
        <div class="news-break">
            <div class="title">Амралт баяр ёслол</div>
            <div class="row">
                <div class="col-md-4">
                    <div class="trans-box">
                        <div class="thumb">
                            <img src="http://www.argillabrewing.com/wp-content/images/pizza3.png" width="230" height="184">
                        </div>
                        <h1>Baking Fruits</h1>
                        <div class="description">Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio facilisis turpis nunc in odio</div>
                        <div class="more-link"><a href="#">Read more</a></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="trans-box">
                        <div class="thumb">
                            <img src="http://www.argillabrewing.com/wp-content/images/pizza3.png" width="230" height="184">
                        </div>
                        <h1>Baking Fruits</h1>
                        <div class="description">Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="trans-box">
                        <div class="thumb">
                            <img src="http://www.argillabrewing.com/wp-content/images/pizza3.png" width="230" height="184">
                        </div>
                        <h1>Baking Fruits</h1>
                        <div class="description">Nam ornare, erat eget porttitor tristique, nunc ante euismod diam, eu facilisis turpis nunc in odio</div>
                    </div>
                </div>
            </div>

        </div>
        <div class="news-break">
            <div class="calendar-title">Event Calendar</div>
            <div class="event-top row">
                <div class="col-md-4 pr0">
                    <div class="event-left">
                        <div class="today">
                            <h1>23</h1>
                            <h2>6 сар</h2>
                            <h4>Даваа</h4>
                        </div>
                        <div class="prev"><a href="#"><span></span></a></div>
                        <ol class="carousel-linked-nav eventpagination">
                            <li class="active"><a href="#1">1</a></li>            </ol>
                        <div class="next"><a href="#"><span></span></a></div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="eventswiper">
                        <div id="myCarousel" class="carousel slide" data-ride="carousel">


                            <!-- Wrapper for slides -->
                            <div class="carousel-inner">
                                <div class="item active">
                                    <img width="750" height="393" src="http://localhost:8888/mymenu/resources/uploads/2014/06/Cocktail-1-750x393.jpg" class="attachment-main-slider wp-post-image" alt="Cocktail 1">                        <div class="carousel-caption">
                                        <div class="title">
                                            <a href="http://localhost:8888/mymenu/events/123123/">
                                                123123                                </a>
                                        </div>
                                        <div class="date">00:00 - 00:00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Controls -->
                            <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="right carousel-control" href="#myCarousel" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<?php endif; ?>
