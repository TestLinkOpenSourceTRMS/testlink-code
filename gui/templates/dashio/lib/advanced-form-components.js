//date picker start

    if (top.location != location) {
        top.location.href = document.location.href ;
    }
    $(function(){
        window.prettyPrint && prettyPrint();
        $('.default-date-picker').datepicker({
            format: 'mm-dd-yyyy'
        });
        $('.dpYears').datepicker();
        $('.dpMonths').datepicker();


        var startDate = new Date(2012,1,20);
        var endDate = new Date(2012,1,25);
        $('.dp4').datepicker()
            .on('changeDate', function(ev){
                if (ev.date.valueOf() > endDate.valueOf()){
                    $('.alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('.alert').hide();
                    startDate = new Date(ev.date);
                    $('#startDate').text($('.dp4').data('date'));
                }
                $('.dp4').datepicker('hide');
            });
        $('.dp5').datepicker()
            .on('changeDate', function(ev){
                if (ev.date.valueOf() < startDate.valueOf()){
                    $('.alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('.alert').hide();
                    endDate = new Date(ev.date);
                    $('.endDate').text($('.dp5').data('date'));
                }
                $('.dp5').datepicker('hide');
            });

        // disabling dates
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        var checkin = $('.dpd1').datepicker({
            onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function(ev) {
                if (ev.date.valueOf() > checkout.date.valueOf()) {
                    var newDate = new Date(ev.date)
                    newDate.setDate(newDate.getDate() + 1);
                    checkout.setValue(newDate);
                }
                checkin.hide();
                $('.dpd2')[0].focus();
            }).data('datepicker');
        var checkout = $('.dpd2').datepicker({
            onRender: function(date) {
                return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function(ev) {
                checkout.hide();
            }).data('datepicker');
    });

//date picker end


//datetime picker start

$(".form_datetime").datetimepicker({format: 'yyyy-mm-dd hh:ii'});

$(".form_datetime-component").datetimepicker({
    format: "dd MM yyyy - hh:ii"
});

$(".form_datetime-adv").datetimepicker({
    format: "dd MM yyyy - hh:ii",
    autoclose: true,
    todayBtn: true,
    startDate: "2013-02-14 10:00",
    minuteStep: 10
});

$(".form_datetime-meridian").datetimepicker({
    format: "dd MM yyyy - HH:ii P",
    showMeridian: true,
    autoclose: true,
    todayBtn: true
});

//datetime picker end

//timepicker start
$('.timepicker-default').timepicker();


$('.timepicker-24').timepicker({
    autoclose: true,
    minuteStep: 1,
    showSeconds: true,
    showMeridian: false
});

//timepicker end

