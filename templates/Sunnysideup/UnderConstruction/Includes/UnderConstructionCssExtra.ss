<style>
    body, html {
      background: $UnderConstructionBackgroundColour;
      color: $UnderConstructionForegroundColour;
    }

    <% if $UnderConstructionImageName %>
    .bgimg {
       background-image: url('$UnderConstructionImageName');
    }
    <% end_if %>
</style>
