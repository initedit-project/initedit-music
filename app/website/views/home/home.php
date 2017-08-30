<div class="pageContainer homeContainer">
    <div class="page home">
        <div class="homeSliderContainer " id="topHomeSlider">
            <div class="homeSlider">
                <div class="sliderItem" style='background-image: url("/public/images/banner/banner1.jpg"),linear-gradient(135deg,#18F3AD,#AAA);'></div>
                <div class="sliderItem" style='background-image: url("/public/images/banner/banner2.jpg"),linear-gradient(135deg,#18F3AD,#AAA);'>
                    <a href="/surprise" class="surpriseText">
                        Surprise Me
                        <br/>
                        : &nbsp;)
                    </a>
                </div>
                <div class="sliderItem" style='background-image: url("/public/images/banner/banner3.jpg"),linear-gradient(135deg,#18F3AD,#AAA);'>
                    <a href="/surprise" class="bestOfYear">
                        The
                        <br/>
                        Best Songs Of 2016
                    </a>
                </div>
            </div>
        </div>
        <div class="homeMenu">
            <a href="/trending" class="highlight">Trending</a>
            <a href="/new">New</a>
        </div>

        <div id="homeTrending"></div>
    </div>
    <div class="paggingContainer layout-content">
        <div class="paging">
            <a href="/trending"><button class="button cta">More</button></a>
        </div>
    </div>
</div>
<script>IniteditMusic.page.home.slider.init();</script>