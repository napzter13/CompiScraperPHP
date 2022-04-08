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
        updateLogoStatus('up');
        $.ajax({
            type: "GET",
            url: "../clearCache",
            error: function (request, status, error) {
                toastError(error);
                updateLogoStatus('down');
            },
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
            updateLogoStatus('down');
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
            updateLogoStatus('up');
            $.ajax({
                type: "POST",
                url: "../getDataByUrl",
                dataType: 'json',
                data: {
                    'urls': url
                },
                error: function (request, status, error) {
                    $('#results').append('<tr><td colspan="4" style="color:darkred;">' + error + ': ' + url + '</td></tr>');
                    loaded += 1;
                    $('#resultsLoaded').html(loaded);
                    updateLogoStatus('down');
                },
            }).done(function(data) {
                data.forEach(dat => {
                    if (dat['success'] == true) {
                        var names_s = '';
                        var prices_s = '';
                        Object.values(dat.data.names).forEach(name => {
                            names_s += name + '<br>';
                        });
                        Object.values(dat.data.prices).forEach(price => {
                            prices_s += price + '<br>';
                        });
                        $('#results').append('<tr><td>' + dat.data.domain + '</td><td>' + names_s + '</td><td style="text-align:right;">' + prices_s + '</td></tr>');
                        $('#getDataByUrl #values').val($('#getDataByUrl #values').val().replaceAll(url, dat.data.url));
                    } else {
                        $('#results').append('<tr><td colspan="4" style="color:darkred;">' + dat.message + '</td></tr>');
                    }
                    loaded += 1;
                    $('#resultsLoaded').html(loaded);
                    $("tr:odd").css({
                        "background-color":"#1b202c",
                        "color":"rgb(163 170 182);"});
                    $('.table-responsive').animate({ scrollTop: 99999 }, 1000);
                });
                updateLogoStatus('down');
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
            updateLogoStatus('up');
            $.ajax({
                type: "POST",
                url: "../getUrlsByProduct",
                dataType: 'json',
                data: {
                    'names': name
                },
                error: function (request, status, error) {
                    toastError(error);
                    updateLogoStatus('down');
                },
            }).done(function(data) {
                data = data[0];
                if (data['success'] == true) {
                    Object.values(data.urls).forEach(url => {
                        $('#getDataByUrl #values').val($('#getDataByUrl #values').val() + url + '\n');
                    });
                    $('#getDataByProduct #values').val($('#getDataByProduct #values').val().replaceAll(name + '\\r', ''));
                    data.product_names.forEach(product_name => {
                        $('#getDataByProduct #values').val($('#getDataByProduct #values').val() + product_name + '\n');
                    });
                    updateCountGetDataByProduct();
                } else {
                    toastError(data.message);
                }
                updateLogoStatus('down');
            });
        }
    });
}

var jobsRunning = 0;
function updateLogoStatus(direction = null) {
    if (direction == 'up') {
        jobsRunning += 1;
    } else if (direction == 'down') {
        jobsRunning -= 1;
    }
    if (jobsRunning > 0) {
        $('h1').css('opacity', '0.5');
        $('title').html('scraper.studio [*' + jobsRunning + ']');
        $('#title').html('scraper.studio [*' + jobsRunning + ']');
    } else {
        $('h1').css('opacity', '0.9');
        $('title').html('scraper.studio [ready]');
        $('#title').html('scraper.studio [ready]');
    }
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
