function changetext() {
    var ind = document.getElementById("query").value;
    //window.alert(ind);
    if (ind =='bgp')
    {
        //window.alert("BGP selected");
        document.getElementById("fqdn").innerHTML = "IP Address:";
        document.getElementById("myTable").style.visibility = "hidden";
        document.getElementById("myTable2").style.visibility= "visible";
        document.getElementById('divIPv46').style.visibility='hidden';
    }
    else
    {
        //window.alert("NOT BGP selected");
        document.getElementById("fqdn").innerHTML = "FQDN or IP Address:";
        document.getElementById('myTable').style.visibility='visible';
        document.getElementById('myTable2').style.visibility='hidden';
    }
    var rad;
    for (var i=0; i < document.lg.sourceIP.length; i++)
    {
        if (document.lg.sourceIP[i].checked)
        {
            rad = document.lg.sourceIP[i].value;
        }
    }
    //window.alert(rad);
    if (rad == 'IP')
    {
        document.lg.addr.disabled= false;
        document.lg.addrFQDN.disabled = "true";
        document.getElementById('divIPv46').style.visibility='hidden';
    }
    else if (rad == 'FQDN' && ind != "bgp")
    {
        document.lg.addrFQDN.disabled= false;
        document.lg.addr.disabled= "true";
        document.getElementById('divIPv46').style.visibility='visible';
    }
    else
    {
        document.lg.addr.disabled= "true";
        document.lg.addrFQDN.disabled = "true";
        document.getElementById('divIPv46').style.visibility='hidden';
    }
}