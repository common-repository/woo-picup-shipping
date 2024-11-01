<?php

$newOptions = [];

if (!empty($options)) {
    foreach ($options as $id => $option) {
        $newOptions[] = $options[$id];
    }
}

$jsonOptions = json_encode($newOptions, true);


?>
<link href="https://www.picup.co.za/assets/css/style.css" rel="stylesheet" />
<div class="container-fluid">
            <div class="row" style="border-bottom: 1px solid black; padding-bottom: 10px">
                <div class="col-md-8">
                    <label>Description</label>
                    <input id="picupDescription" class="form-control" type="text" name="description" placeholder="Description" required  data-validate='{"required":true}'>
                </div>
                <div class="col-md-2 text-right">
                    <label>Price</label>
                    <input id="picupPrice" class="form-control text-right" type="text" name="price" placeholder="0.00" >
                </div>
                <div class="col-md-2 text-right">
                    <br>
                    <input type="button" name="add" value="Add Option" class="btn btn-primary" onclick="addOption(  document.getElementById('picupDescription').value, document.getElementById('picupPrice').value); clearOptionInputs();">
                </div>
            </div>
            <div class="row" style="border-bottom: 1px solid black; padding-bottom: 10px; margin-bottom: 10px">
               <div class="col-md-8">
                    <strong>Description</strong>
               </div>
                <div class="col-md-2 text-right">
                    <strong>Price</strong>
                </div>
                <div class="col-md-2 text-right">
                    <strong>Action</strong>
                </div>
            </div>

            <div id="renderOptions">

            </div>
    </form>
</div>
<script>
    var options = <?=$jsonOptions?>;
    var optionCount = options.length;



    console.log('Options', options, optionCount);

    function renderOptions (options) {
        for (var i = 0; i < options.length; i++) {
            if (options[i].price === undefined) options[i].price = 0.00;
            if (options[i].description === undefined) options[i].description = 'Generic '+ options[i].price;
            addOption(options[i].description, options[i].price)
        }
    }


    function clearOptionInputs() {
        document.getElementById('picupDescription').value = '';
        document.getElementById('picupPrice').value = '';
    }

    function addOption(description, price) {
        if (description === ''  || price === '' ) {
            alert ('Please complete all the fields!');
            return  false;
        }

        let optionDiv = document.getElementById('renderOptions');
        let row = document.createElement('div');
        row.setAttribute('class', 'row');

        row.innerHTML += '<div class="col-md-8"><input type="text" class="form-control" name="options_'+optionCount+'_description" value="'+description+'"></div>';
        row.innerHTML += '<div class="col-md-2 text-right"><input type="text" class="form-control text-right"  name="options_'+optionCount+'_price" value="'+price+'"></div>';
        row.innerHTML += '<div class="col-md-2 text-right"><button class="btn btn-danger" onclick="if (confirm(\'Are you sure you want to delete this option?\')) {  deleteOption(this) } ">Delete</button></div>';
        row.innerHTML += '<div class="col-md-12"><hr></div>';

        optionDiv.appendChild(row);
        optionCount++;

    }

    function deleteOption(row) {
        console.log (row.parentElement.parentElement);
        let optionDiv = document.getElementById('renderOptions');
        optionDiv.removeChild(row.parentElement.parentElement);
    }

    renderOptions (options);

    console.log('Done!');
</script>


