window.onload = getLocationConstant;

function getLocationConstant()
{
    if(navigator.geolocation)
    {
        navigator.geolocation.getCurrentPosition(onGeoSuccess,onGeoError);  
    } else {
        alert("Your browser or device doesn't support Geolocation");
    }
}

// If we have a successful location update
function onGeoSuccess(event)
{
    document.getElementById("latitude").value =  event.coords.latitude; 
    document.getElementById("longitude").value = event.coords.longitude;

}


