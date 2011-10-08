$eshopj=jQuery.noConflict();

$eshopj(function () { // this line makes sure this code runs on page load
	$eshopj('#checkAllAuto').click(function () {
		$eshopj(this).parents('table:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
});