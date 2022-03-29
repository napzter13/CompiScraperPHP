
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


// EventListeners

window.addEventListener("DOMContentLoaded", () => {

    if (localStorage.getDataByProduct != undefined) {
        $('#getDataByProduct textarea').val(localStorage.getDataByProduct);
    }
    if (localStorage.getDataByUrl != undefined) {
        $('#getDataByUrl textarea').val(localStorage.getDataByUrl);
    }
    if (localStorage.dataBlock != undefined) {
        $('#dataBlock').html(JSON.parse(localStorage.dataBlock));
    }

    $(window).bind('beforeunload', function() {
        localStorage.getDataByProduct = $('#getDataByProduct textarea').val();
        localStorage.getDataByUrl = $('#getDataByUrl textarea').val();
        localStorage.dataBlock = JSON.stringify($('#dataBlock').html());
    });

    $("#clear_cache_button").click(function() {
        $.ajax({
            type: "GET",
            url: "../clearCache",
        }).done(function(data) {
            if (data == 1) {
                Toastify({
                    text: "Cleared cache!",
                    duration: 5000,
                    newWindow: true,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    style: {
                      background: "linear-gradient(to right, #00b09b, #96c93d)",
                    },
                    onClick: function() { }
                  }).showToast();
            } else {
                toastError();
            }
        });
    });
    $("#getDataByProduct #goButton").click(function() {
        getUrlsByProduct($('#getDataByProduct #values').val());
    });
    $('#getDataByProduct textarea').on('keyup',function(e) {
        if(e.which == 16) {
            getUrlsByProduct($('#getDataByProduct #values').val());
        }
    });
    $('#getDataByProduct textarea').bind('input propertychange', function() {
        updateCountGetDataByProduct();
    });
    $("#getDataByUrl #goButton").click(function() {
        getDataByUrls($('#getDataByUrl #values').val());
    });
    $('#getDataByUrl textarea').on('keyup',function(e) {
        if(e.which == 16) {
            getDataByUrls($('#getDataByUrl #values').val());
        }
    });
    $('#getDataByUrl textarea').bind('input propertychange', function() {
        updateCountGetDataByUrl();
    });

    updateCountGetDataByProduct();
    updateCountGetDataByUrl();
});



function updateCountGetDataByProduct() {
    var lines = $('#getDataByProduct textarea').val().split('\n');
    $('#productNamesTotal').html(lines.filter(x => x.length > 2).length);
}
function updateCountGetDataByUrl() {
    var lines = $('#getDataByUrl textarea').val().split('\n');
    $('#productUrlsTotal').html(lines.filter(x => x.includes('http')).length);
}

function getDataByUrls(urls) {
    $('#results').html('');
    $('#resultsLoaded').html(0);
    $('#resultsTotal').html(0);
    var loaded = 0;
    var total = 0;

    urls = urls.split(/\r?\n/);

    urls.forEach(url => {
        if (url.includes('http')) {
            $.ajax({
                type: "POST",
                url: "../getDataByUrl",
                dataType: 'json',
                data: {
                    'urls': url
                },
            }).done(function(data) {
                data.forEach(dat => {
                    if (dat['success'] == true) {
                        var prices_s = '';
                        Object.values(dat.data.prices).forEach(price => {
                            prices_s += price + '<br>';
                        });
                        $('#results').append('<tr><td>' + dat.data.domain + '</td><td>' + dat.data.names.join('<br>') + '</td><td style="text-align:right;">' + prices_s + '</td></tr>');
                        $('#getDataByUrl #values').val($('#getDataByUrl #values').val().replaceAll(url, dat.data.url));
                    } else {
                        $('#results').append('<tr><td colspan="4" style="color:darkred;">' + dat.message + '</td></tr>');
                    }
                    loaded += 1;
                    $('#resultsLoaded').html(loaded);
                    $("tr:odd").css({
                        "background-color":"#1b202c",
                        "color":"rgb(163 170 182);"});
                });
            });

            total += 1;
            $('#resultsTotal').html(total);
        }
    });
}

function getUrlsByProduct(names) {
    $('#getDataByUrl #values').val('');

    names = names.split(/\r?\n/);

    names.forEach(name => {
        if (name.length > 2) {
            $.ajax({
                type: "POST",
                url: "../getUrlsByProduct",
                dataType: 'json',
                data: {
                    'names': name
                },
            }).done(function(data) {
                data = data[0];
                if (data['success'] == true) {
                    Object.values(data.urls).forEach(url => {
                        $('#getDataByUrl #values').val($('#getDataByUrl #values').val() + url + '\n');
                    });
                    updateCountGetDataByUrl();
                } else {
                    toastError(data.message);
                }
            });
        }
    });
}

function toastError(message = '') {
    Toastify({
        text: "Error: " + message,
        duration: 5000,
        newWindow: true,
        close: true,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
        style: {
          background: "linear-gradient(to right, red, #96c93d)",
        },
        onClick: function() { }
      }).showToast();
}
