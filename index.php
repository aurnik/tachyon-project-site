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
    <!--img src="https://flink.apache.org/img/flink-stack-small.png"-->
    {% markdown home.md %}
    <div id="ticker">
        {% include getData.php %}
        <!--
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
        -->
    </div>

</div>
<script src="../js/moment.min.js"></script>
<script>
    var stats = {
        contributors: document.getElementById("contributors"),
        commits: document.getElementById("commits"),
        age: document.getElementById("age"),
        stats: document.getElementById("stats")
    };
    var contributorCount, commitCount, age;
    if(storageAvailable && (!sessionStorage.contributorCount || !sessionStorage.commitCount) || !storageAvailable) {
        var res = <?php echo json_encode($contributors); ?>;
        var contributors = [];
        for (var i = 0; i < res.length; i++) {
            contributors = contributors.concat(res[i]);
        }
        var commits = 0;
        contributorCount = contributors.length.toLocaleString();

        for(var i = 0; i < contributors.length; i++) {
            commits += contributors[i].contributions;
        }
        commitCount = commits.toLocaleString();

        stats.contributors.innerHTML = contributorCount + " contributors";
        stats.commits.innerHTML = commitCount + " commits";


        if(storageAvailable) {
            sessionStorage.setItem("contributorCount", contributorCount);
            var contributorsMin = [];
            // minimize response for sessionStorage
            for (var i = 0; i < contributors.length; i++) {
                contrib = {
                    login: contributors[i].login,
                    avatar_url: contributors[i].avatar_url,
                    contributions: contributors[i].contributions
                }
                contributorsMin.push(contrib);
            }
            sessionStorage.setItem("contributors", JSON.stringify(contributorsMin));
            sessionStorage.setItem("commitCount", commitCount);
        }

        stats.stats.classList.add('visible');
        var stars = <?php echo $stars; ?>;
        document.getElementById("starCount").classList.add('visible');
        document.getElementById("starCount").innerHTML = stars.toLocaleString();
        if(storageAvailable) {
            sessionStorage.setItem("starCount", stars.toLocaleString());
        }


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
