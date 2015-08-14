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

    <div class="ticker">

    </div>
    <!--img src="https://flink.apache.org/img/flink-stack-small.png"-->
    {% markdown home.md %}
    <div id="ticker">
        <div class="item">
            Random announcement
        </div>
        <div class="item event">
            <div class="calendar">
                <div class="month">
                    SEP
                </div>
                <div class="date">
                    5
                </div>
            </div>
            <div class="content">
                <h1>Tachyon @ Flink Summit</h1>
                <p>
                    Tachyon will have a presentation at Flink Summit
                </p>
            </div>
        </div>
        <div class="item release">
            <h1>
                Tachyon 0.7.0
            </h1>
            <p>
                We are excited to announce Tachyon v0.7.0, our largest release to date with a large number of new features, significant code base improvements, and a...
            </p>
            <div class="footnote">
                via GitHub
            </div>
        </div>
        <div class="item meetup">
            <div class="calendar">
                <div class="month">
                    SEP
                </div>
                <div class="date">
                    2
                </div>
            </div>
            <div class="content">
                <h1>First Tachyon Meetup</h1>
                <p>
                    We will be holding our first Tachyon meetup at Tachyon Nexus this weekend!
                </p>
                <div class="footnote">
                    via Meetup.com
                </div>
            </div>
        </div>
        <div class="item media">
            <div class="content">
                <h1>Tachyon: Reliable, Memory Speed Storage for Cluster...</h1>
                <p>
                    Tachyon is a distributed file system enabling reliable data sharing at memory speed across cluster computing frameworks. While caching today improves...
                </p>
                <div class="footnote">
                    via cs.berkeley.edu
                </div>
            </div>
        </div>
    </div>

</div>
{% include meetup.php %}
<script src="../js/moment.min.js"></script>
<script src="../js/githubConnect.js"></script><!-- File with GitHub key -->
<script>
    var news = [];

    var stats = {
        contributors: document.getElementById("contributors"),
        commits: document.getElementById("commits"),
        age: document.getElementById("age"),
        stats: document.getElementById("stats")
    };
    var contributorCount, commitCount, age;
    if(storageAvailable && (!sessionStorage.contributorCount || !sessionStorage.commitCount) || !storageAvailable) {var contributors = [];
        var commits = 0;

        var getAll = function(err, val) {
            if(!err) {
                contributors = contributors.concat(val);
                if(val.nextPage) {
                    val.nextPage(getAll);
                }
                else { // done fetching results
                    contributorCount = contributors.length.toLocaleString();

                    for(var i = 0; i < contributors.length; i++) {
                        commits += contributors[i].contributions;
                    }
                    commitCount = commits.toLocaleString();

                    stats.contributors.innerHTML = contributorCount + " contributors";
                    stats.commits.innerHTML = commitCount + " commits";


                    if(storageAvailable) {
                        sessionStorage.setItem("contributorCount", contributorCount);
                        sessionStorage.setItem("contributors", JSON.stringify(contributors));
                        sessionStorage.setItem("commitCount", commitCount);
                    }

                    stats.stats.classList.add('visible');
                }
            }
        };

        octo.repos('amplab', 'tachyon').contributors.fetch(getAll);

        octo.repos('amplab', 'tachyon').fetch(function(err, val) {
            document.getElementById("starCount").classList.add('visible');
            document.getElementById("starCount").innerHTML = val.stargazersCount.toLocaleString();
            if(storageAvailable) {
                sessionStorage.setItem("starCount", val.stargazersCount.toLocaleString());
            }
        });

        octo.repos('amplab', 'tachyon').releases.fetch(function(err, val) {
            // get all releases within the last month
            var recentReleasesCount = 0;
            var earliestRelease = val[0].createdAt;
            var today = (new Date()).getTime();
            var month = 1000 * 60 * 60 * 24 * 30;
            while(earliestRelease.getTime() + month > today) {
                earliestRelease = val[recentReleasesCount].createdAt;
                recentReleasesCount++;
            }
            recentReleasesCount--; // remove the first release more than a month old
            var releases = val.slice(0,recentReleasesCount);
            for(var i = 0; i < recentReleasesCount; i++) {
                news.push({
                    date: releases[i].createdAt.getTime(),
                    title: releases[i].name,
                    content: releases[i].body.slice(0,150).replace(/(\r\n|\n|\r)/gm,"") + "..."
                });
            }
        });


        age = Math.round((moment.duration(moment() - moment("2012-12-21 09:43:46-08:00"))).asYears() * 100) / 100;
        if(storageAvailable && !sessionStorage.age) {
            sessionStorage.setItem("age", age);
        }

        stats.age.innerHTML = age + " years old";
    }
    else {
        contributorCount = sessionStorage.contributorCount;
        commitCount = sessionStorage.commitCount;
        age = sessionStorage.age;

        stats.contributors.innerHTML = contributorCount + " contributors";
        stats.commits.innerHTML = commitCount + " commits";
        stats.age.innerHTML = age + " years old";
        stats.stats.classList.add('visible');

        document.getElementById("starCount").classList.add('visible');
        document.getElementById("starCount").innerHTML = sessionStorage.starCount;
    }


</script>
