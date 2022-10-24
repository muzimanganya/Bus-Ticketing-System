$(document).ready(function() {
    
    $('a.bus-loader').on('click', function(e){
        var url = $(this).attr('href');

        //clear classes
        $('table.bus-table').find('td').each (function() {
            $(this).children().removeClass('btn-danger');
            $(this).children().removeClass('btn-success');
            $(this).children().addClass('btn-primary');
            //remove popups
            $(this).children().removeAttr('data-toggle').removeAttr('title').removeAttr('data-content');
            $('[data-toggle="popover"]').popover('destroy');
        });  
        
        $.getJSON(url, function(json) { 
            var seatsOccupied = {};
            
            for (var v in json) {
               if (json.hasOwnProperty(v)) {  
                    var seatid = 'seat_'+json[v].seat;
                    //add popup
                    var title = 'Seat Details';
                    var ticket = chunk_split(json[v].ticket, '3', '-');
                    ticket = ticket.substring(0,(ticket.length-1));
                    
                    $('#'+seatid).removeClass('btn-primary');
                    if(json[v].status=='CO')
                        $('#'+seatid).addClass('btn-danger'); 
                    else
                        $('#'+seatid).addClass('btn-success'); 
                    
                    var content = "<p><b>"+ticket+"</b><br>"+json[v].owner+"<br>"+json[v].rname+"</p>"; 
                    content = content+"<a href='"+json[v].change_seat+"' class='action-change glyphicon glyphicon-pencil'>Change</a>";
                    content = content+"&nbsp;&nbsp;&nbsp;&nbsp;<a href='"+json[v].cancel+"' class='action-cancel glyphicon glyphicon-remove'>Cancel</a>";
                    //if exists append contents
                    if(seatid in seatsOccupied)
                    {
                        content = content+"<hr>"+$('#'+seatid).attr('data-content');
                    }
                    $('#'+seatid).attr('data-toggle', 'popover');
                    $('#'+seatid).attr('title', title);
                    $('#'+seatid).attr('data-content', content);
                    
                    //add key to the object
                    seatsOccupied[seatid] = true;
               }
            } 
            //setup popovers
            $('[data-toggle="popover"]').popover({
                html: true
            }); 
             
        });
        e.preventDefault();
    });
    
    $(document).on('click', '.action-change', function(e){
        var info =  $(this).siblings( "p" ).html();
        var url = $(this).attr('href');
        swal({
          title: "Change Seat Number",
          text: "Please Enter new seat number for ticket:<br>"+info,
          html:true,
          type: "input",
          showCancelButton: true,
          closeOnConfirm: false,
          animation: "slide-from-top",
          inputPlaceholder: "New Seat Number"
        },
        function(inputValue){
          if (inputValue === false) return false;

            if (inputValue === "" || parseInt(inputValue)<1) {
                swal.showInputError("Please Enter valid Seat");
                return false
            }
            url = url+"&seat="+inputValue;
            $.getJSON(url, function(json) { 
                swal({title:"Operation Completed!", text:json.message, showConfirmButton: true});
                if(json.success)
                    setTimeout(function(){  location.reload(); }, 3000); 
            });
        });
        e.preventDefault();
    });
    
    $(document).on('click', '.action-cancel', function(e){
        var info =  $(this).siblings( "p" ).html();
        var url = $(this).attr('href');
        swal({
          title: "Want to cancel the Ticket?",
          text: info,
          html: true,
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, Cancel it!",
          cancelButtonText: "No, Don't!",
          closeOnConfirm: false,
          showLoaderOnConfirm: true,
          closeOnCancel: false
        },
        function(isConfirm){
            if(isConfirm) {
                $.getJSON(url, function(json) { 
                    swal({title:"Operation Completed!", text:json.message, showConfirmButton: false});
                    setTimeout(function(){  location.reload(); }, 3000); 
                });
            } 
            else {
                swal("Operation Cancelled", "Your Ticket is safe and unchanged!", "error");
            }
        });
        e.preventDefault();
    });
});

function chunk_split (body, chunklen, end) {
	// http://jsphp.co/jsphp/fn/view/chunk_split
	// + original by: Paulo Freitas
	// + input by: Brett Zamir (http://brett-zamir.me)
	// + bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// + improved by: Theriault
	// * example 1: chunk_split('Hello world!', 1, '*');
	// * returns 1: 'H*e*l*l*o* *w*o*r*l*d*!*'
	// * example 2: chunk_split('Hello world!', 10, '*');
	// * returns 2: 'Hello worl*d!*'
	chunklen = parseInt(chunklen, 10) || 76;
	end = end || '\r\n';

	if (chunklen < 1) {
		return false;
	}

	return body.match(new RegExp(".{0," + chunklen + "}", "g")).join(end);

}
