<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key={{env('GOOGLE_MAPS_API_KEY')}}"></script>
<script>
    var autocomplete_start_location = new google.maps.places.Autocomplete($("#start_location")[0], {});
    var autocomplete_end_location = new google.maps.places.Autocomplete($("#end_location")[0], {});

    google.maps.event.addListener(autocomplete_start_location, 'place_changed', function() {
        var place = autocomplete_start_location.getPlace();
    });

    google.maps.event.addListener(autocomplete_end_location, 'place_changed_end_location', function() {
        var place_end = autocomplete_end_location.getPlace();
    });
</script>