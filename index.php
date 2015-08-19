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
    {% markdown home.md %}
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
    var stats = {
        contributors: document.getElementById("contributors"),
        commits: document.getElementById("commits"),
        age: document.getElementById("age"),
        stars: document.getElementById("starCount"),
        stats: document.getElementById("stats")
    };
    var contributorCount, commitCount, age, stars;
    // TODO: Only pull each piece of info when needed
    if(storageAvailable && (!sessionStorage.contributorCount || !sessionStorage.commitCount || !sessionStorage.starCount || !sessionStorage.age) || !storageAvailable) {

        console.log("hi");
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

        var stars = <?php echo $stars; ?>;
        stats.stars.classList.add('visible');
        stats.stars.innerHTML = stars.toLocaleString();
        if(storageAvailable) {
            sessionStorage.setItem("starCount", stars.toLocaleString());
        }


        age = Math.round((moment.duration(moment() - moment("2012-12-21 09:43:46-08:00"))).asYears() * 100) / 100;
        if(storageAvailable && !sessionStorage.age) {
            sessionStorage.setItem("age", age);
        }

        stats.age.innerHTML = age + " years old";

        stats.stats.classList.add('visible');
    }
    else {
        contributorCount = sessionStorage.contributorCount;
        commitCount = sessionStorage.commitCount;
        age = sessionStorage.age;
        stars = sessionStorage.starCount;

        stats.contributors.innerHTML = contributorCount + " contributors";
        stats.commits.innerHTML = commitCount + " commits";
        stats.age.innerHTML = age + " years old";
        stats.stats.classList.add('visible');

        stats.stars.innerHTML = stars;
        stats.stars.classList.add('visible');
    }


</script>
