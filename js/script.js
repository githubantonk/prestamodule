$(function () {

	var num_old = 1;

	$(".direction-switch").click(function(){
		from_ = $("select[name=from_]").val();
		$("select[name=from_]").val($("select[name=to_]").val());
		$("select[name=to_]").val(from_);
		convert();
	});

	$('input[name=num]').bind('keydown', function(e) {
		num_old = $(this).val();
	});

	$('input[name=num]').bind('mouseup keyup cut copy paste', function(e) {
		convert();
		num_old = $(this).val();
	});

	$("select").change(function() {
		convert();
	});

	convert();

	function convert(){
		var num = $("input[name=num]").val();
		if ($.isNumeric(num) && num > 0 && (num_old != num || (num == 1 && num_old == 1))) {
			price_from = $("select[name=from_] :selected").data("price");
			price_to = $("select[name=to_] :selected").data("price");
			if (price_to) {
				$("input[name=num2]").val((num * price_from / price_to ).toFixed(4));
				$.ajax({
				    type: "POST",
				    url: '/index.php',
				    data: "fc=module&module=mymodule&controller=historyadd&ajax=1&num="+num+"&from_="+$("select[name=from_] :selected").data("symbol")+"&to_="+$("select[name=to_] :selected").data("symbol"),
				    success: function(res){
				    	$("[data-history10]").html(res);
				    },
				    error: function() {
				        console.log("ERROR:");
				    }
				});
			}
			else $("#res").html('none');
		}
	};

});