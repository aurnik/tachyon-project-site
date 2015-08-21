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
<script type="text/javascript">
    var plot = Bubbles();
    Lib.ajax.getJSON({
        url: '../cache/contributors.json'
    }, function(res) {
        var contributors = JSON.parse(res);
        root.plotData("#vis", contributors, plot);
    });
</script>

<div class="wrapper">
    {% markdown community.md %}
</div>

{% include contributors.php %}
