---
layout: default
title: Community
permalink: /community/index.php
---
 <link rel="stylesheet" href="../css/style.css">
{% capture bannerContent %}
    Community
{% endcapture %}
{% include banner.html content=bannerContent %}

<div id="vis"></div>

<script src="../js/libs/d3.min.js"></script>
<script src="../js/contributorcloud.js"></script>
{% include contributors.php %}
<script type="text/javascript">
    if(storageAvailable && sessionStorage.contributors) { // web storage supported
        var plot = Bubbles();
        root.plotData("#vis", JSON.parse(sessionStorage.contributors), plot);
    } else {
        var plot = Bubbles();
        var commits = 0;
        console.log("hi");
        var res = <?php echo json_encode($contributors); ?>;
        console.log(res);
        var contributors = [];
        for (var i = 0; i < res.length; i++) {
            contributors = contributors.concat(res[i]);
        }
        for(var i = 0; i < contributors.length; i++) {
            commits += contributors[i].contributions;
        }
        if(storageAvailable) {
            sessionStorage.setItem("commitCount", commits.toLocaleString());
            sessionStorage.setItem("contributorCount", contributors.length.toLocaleString());
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
        }

        root.plotData("#vis", contributors, plot);
    }
</script>

<div class="wrapper">
    {% markdown community.md %}
</div>
