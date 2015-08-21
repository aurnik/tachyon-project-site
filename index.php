---
layout: default
title: Home
---
{% capture bannerContent %}
    <strong>Tachyon</strong> is a memory-centric distributed storage system <br /> enabling reliable data sharing at memory-speed across cluster frameworks.
    <a href="https://github.com/amplab/tachyon" class="githubLink">
        <div id="callToAction">
            <i class='fa fa-star'></i>
            Star
        </div>
    </a>
    <div id="starCount">
    </div>
    <div id="stats">
        <div class="stat">
            <i class='fa fa-male'></i>
            <div id="contributors">

            </div>
        </div>
        <div class="stat">
            <i class='fa fa-code'></i>
            <div id="commits">

            </div>
        </div>
        <div class="stat">
            <i class='fa fa-birthday-cake'></i>
            <div id="age">

            </div>
        </div>
    </div>
{% endcapture %}
{% include banner.html content=bannerContent %}
<div class="wrapper">
    <div id="news">
        UPDATES
        <ul id="newsBullets">

        </ul>
    </div>
    {% markdown home.md %}
    <script>
    (function() {
        var Lib = {
            ajax: {
                xhr: function() {
                    var instance = new XMLHttpRequest();
                    return instance;
                },
                getJSON: function(options, callback) {
                    var xhttp = this.xhr();
                    options.url = options.url || location.href;
                    options.data = options.data || null;
                    callback = callback ||
                    function() {};
                    options.type = options.type || 'json';
                    var url = options.url;
                    if (options.type == 'jsonp') {
                        window.jsonCallback = callback;
                        var $url = url.replace('callback=?', 'callback=jsonCallback');
                        var script = document.createElement('script');
                        script.src = $url;
                        document.body.appendChild(script);
                    }
                    xhttp.open('GET', options.url, true);
                    xhttp.send(options.data);
                    xhttp.onreadystatechange = function() {
                        if (xhttp.status == 200 && xhttp.readyState == 4) {
                            callback(xhttp.responseText);
                        }
                    };
                }
            }
        };

        window.Lib = Lib;
        })()

    var stats = {
        contributors: document.getElementById("contributors"),
        commits: document.getElementById("commits"),
        age: document.getElementById("age"),
        stars: document.getElementById("starCount"),
        stats: document.getElementById("stats")
    };

    var age = Math.round((moment.duration(moment() - moment("2012-12-21 09:43:46-08:00"))).asYears() * 100) / 100;

    Lib.ajax.getJSON({
        url: 'cache/stats.json'
    }, function(res) {
        var statData = JSON.parse(res)[0];
        stats.contributors.innerHTML = statData.contributors + " contributors";
        stats.commits.innerHTML = statData.commits + " commits";
        stats.age.innerHTML = age + " years old";
        stats.stats.classList.add('visible');

        stats.stars.innerHTML = statData.stars;
        stats.stars.classList.add('visible');
    });

    var newsWrapper = document.getElementById("news");
    var newsBox = document.getElementById("newsBullets");

    Lib.ajax.getJSON({
        url: 'cache/news.json'
    }, function(res) {
        var news = JSON.parse(res);
        var newsLen = news.length;
        for(var i = 0; i < newsLen; i++) {
            var listItem = document.createElement("LI");
            var newsItem = document.createElement("A");
            var date = moment(news[i].date);
            newsItem.innerHTML= date.format('MMM Do') + " - " + news[i].title;
            newsItem.href = news[i].link;
            listItem.appendChild(newsItem);
            newsBox.appendChild(listItem);
        }
        newsWrapper.classList.add('visible');
    });

    Lib.ajax.getJSON({
        url: 'cache/events.json'
    }, function(res) {
        var events = JSON.parse(res);
        var eventsLen = events.length;
        var eventsString = "";
        for(var i = 0; i < eventsLen; i++) {
            var newEvent, linkStart, linkEnd, footnote;
            linkStart = linkEnd = footnote = "";
            if(events[i].link != "") {
                linkStart = "<a target='_blank' href='" + events[i].link + "'>";
                linkEnd = "</a>";

                if(events[i].type == "meetup") {
                    footnote = "<div class='footnote'>via Meetup.com</div>";
                }
            }
            var calendar = "<div class='calendar'><div class='month'>" + moment(events[i].date / 1000).format('MMM') + "</div><div class='date'>" +  moment(events[i].date / 1000).format('D') + "</div></div>";
            newEvent = linkStart + "<div class='item " + events[i].type + "'>" + calendar + "<div class='content'><h1>" + events[i].title + "</h1><p>" + events[i].desc + "...</p>" + footnote + "</div></div>" + linkEnd;

            eventsString += newEvent;
        }
        document.getElementById("ticker").getElementsByClassName("container")[0].innerHTML = eventsString;
    });
    </script>
    <div id="ticker">
        <div class="topShadow">

        </div>
        <div class="container">
            {% include getData.php %}
        </div>
        <div class="bottomShadow">

        </div>
    </div>
</div>
<script>


</script>
