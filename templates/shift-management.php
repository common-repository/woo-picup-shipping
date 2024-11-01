<?php
$WEEK_DAYS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

$newShifts = [];

if (!empty($shifts)) {
    foreach ($shifts as $id => $shift) {
        $newShifts[] = $shifts[$id];
    }
}

$jsonShifts = json_encode($newShifts, true);

?>
<link href="https://www.picup.co.za/assets/css/style.css" rel="stylesheet" />
<div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <input type="checkbox" name="displayDeliveryDate" value="true" <?php if ($displayDeliveryDate) { echo "checked"; } ?> > Display projected delivery dates on check outs
                </div>

            </div>
            <div class="row" style="border-bottom: 1px solid black; padding-bottom: 10px">
                <div class="col-md-2">
                    <label>Week Day</label>
                    <select id="picupWeekDay" class="form-control" name="weekDay">
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                        <option value="7">Sunday</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Description</label>
                    <input id="picupDescription" class="form-control" type="text" name="description" placeholder="Description" required  data-validate='{"required":true}'>
                </div>
                <div class="col-md-1">
                    <label>Time From</label>
                    <input id="picupTimeFrom" class="form-control" type="text" name="timeFrom" placeholder="00:00" data-validate='{"validate-time":true}'>
                </div>
                <div class="col-md-1">
                    <label>Time To</label>
                    <input id="picupTimeTo" class="form-control" type="text" name="timeTo" placeholder="00:00" data-validate='{"validate-time":true}'>
                </div>
                <div class="col-md-2 text-right">
                    <label>Price</label>
                    <input id="picupPrice" class="form-control text-right" type="text" name="price" placeholder="0.00" >
                </div>
                <div class="col-md-2">
                    <label>Cut-off Time</label>
                    <input id="picupCutOffTime" class="form-control" type="text" name="cutoffTime" placeholder="12:00" >
                </div>
                <div class="col-md-1 text-right">
                    <br>
                    <input type="button" name="add" value="Add Shift" class="btn btn-primary" onclick="addShift( document.getElementById('picupWeekDay').value, document.getElementById('picupDescription').value, document.getElementById('picupTimeFrom').value, document.getElementById('picupTimeTo').value, document.getElementById('picupPrice').value, document.getElementById('picupCutOffTime').value ); clearShiftInputs();">
                </div>
            </div>
            <div class="row" style="border-bottom: 1px solid black; padding-bottom: 10px; margin-bottom: 10px">
               <div class="col-md-2">
                    <strong>Week Day</strong>
               </div>
               <div class="col-md-3">
                    <strong>Description</strong>
               </div>
               <div class="col-md-1">
                    <strong>Time From</strong>
               </div>
                <div class="col-md-1">
                    <strong>Time to</strong>
                </div>
                <div class="col-md-2 text-right">
                    <strong>Price</strong>
                </div>
                <div class="col-md-2">
                    <strong>Cut-Off Time</strong>
                </div>
                <div class="col-md-1 text-right">
                    <strong>Action</strong>
                </div>
            </div>
            <hr>
            <div id="renderShifts">

            </div>
    </form>
</div>
<script>
    var shifts = <?=$jsonShifts?>;
    var shiftCount = shifts.length;


    if (typeof(weekDays) !== 'undefined') {
        let weekDays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    }
    console.log('Shifts', shifts, shiftCount);

    function renderShifts (shifts) {
        for (var i = 0; i < shifts.length; i++) {
            if (shifts[i].price === undefined) shifts[i].price = 0.00;
            if (shifts[i].cutoffTime == undefined) shifts[i].cutoffTime = shifts[i].end_time;
            if (shifts[i].description === undefined) shifts[i].description = 'Shift '+shifts[i].start_time+' to '+shifts[i].end_time;
            addShift(shifts[i].day, shifts[i].description, shifts[i].start_time, shifts[i].end_time, shifts[i].price, shifts[i].cutoffTime)
        }
    }


    function getWeekDays(weekDay, name) {

        return ' <select id="'+name+'" class="form-control" name="'+name+'">\n' +
            '                        <option value="1">Monday</option>\n' +
            '                        <option value="2">Tuesday</option>\n' +
            '                        <option value="3">Wednesday</option>\n' +
            '                        <option value="4">Thursday</option>\n' +
            '                        <option value="5">Friday</option>\n' +
            '                        <option value="6">Saturday</option>\n' +
            '                        <option value="7">Sunday</option>\n' +
            '                    </select>';

    }

    function clearShiftInputs() {
        document.getElementById('picupWeekDay').value = 1;
        document.getElementById('picupDescription').value = '';
        document.getElementById('picupTimeFrom').value = '';
        document.getElementById('picupTimeTo').value = '';
        document.getElementById('picupPrice').value = '';
        document.getElementById('picupCutOffTime').value = '';
    }

    function addShift(weekDay, description, timeFrom, timeTo, price, cutOffTime) {
        if (description === '' || timeFrom === '' || timeTo === '' || price === '' || cutOffTime === '') {
            alert ('Please complete all the fields!');
            return  false;
        }



        let shiftDiv = document.getElementById('renderShifts');
        let row = document.createElement('div');
        row.setAttribute('class', 'row');
        row.innerHTML = '<div class="col-md-2">'+getWeekDays(weekDay, 'shifts_'+shiftCount+'_weekDay')+'</div>';
        row.innerHTML += '<div class="col-md-3"><input type="text" class="form-control" name="shifts_'+shiftCount+'_description" value="'+description+'"></div>';
        row.innerHTML += '<div class="col-md-1"><input type="text" class="form-control" name="shifts_'+shiftCount+'_timeFrom" value="'+timeFrom+'"></div>';
        row.innerHTML += '<div class="col-md-1"><input type="text" class="form-control" name="shifts_'+shiftCount+'_timeTo" value="'+timeTo+'"></div>';
        row.innerHTML += '<div class="col-md-2 text-right"><input type="text" class="form-control text-right" name="shifts_'+shiftCount+'_price" value="'+price+'"></div>';
        row.innerHTML += '<div class="col-md-2"><input type="text" class="form-control" name="shifts_'+shiftCount+'_cutOffTime" value="'+cutOffTime+'"></div>';
        row.innerHTML += '<div class="col-md-1 text-right"><button class="btn btn-danger" onclick="if (confirm(\'Are you sure you want to delete this shift?\')) {  deleteShift(this) } ">Delete</button></div>';
        row.innerHTML += '<div class="col-md-12"><hr></div>';



        shiftDiv.appendChild(row);

        document.getElementById('shifts_'+shiftCount+'_weekDay').value = weekDay;
        shiftCount++;

    }

    function deleteShift(row) {
        console.log (row.parentElement.parentElement);
        let shiftDiv = document.getElementById('renderShifts');
        shiftDiv.removeChild(row.parentElement.parentElement);
    }

    renderShifts (shifts);

    console.log('Done!');
</script>


