$(document).ready(function() { // don't mess with global namespace, use anonymous function.

	// check if there is a table header
	var header_row = 0;
	if ($('#attendance').find('tbody>tr').first().find('th').length > 0) {
		header_row = 1;
	}

	var deleteRow = function () {
		if ($('#attendance').find('tbody>tr').length == 1 + header_row) {
			// just clear the last row. Do not remove it.
			$(this).parent().next().find('input').val('');
		} else {
			$(this).parent().parent().remove();
		}
	};

	$('#ilpa_add').click(
		function AddRow() {
			var $rows = $('#attendance').find('tbody>tr');
			var $secondLastRow = $rows.last();
			var $newRow = $secondLastRow.clone(true);
			var num_rows = $rows.length - header_row;

			// Reset the label
			$newRow.find('input').first().val('');
			// Increase the row counter
			$newRow.find('input').each(function () {
				var name = $(this).attr('name');
				$(this).attr('name', name.replace(/\[[\d]+\]/, '[' + num_rows + ']'));
			});
			// Insert the row to DOM
			$newRow.insertAfter($secondLastRow);
		});

	// Assign events to the delete buttons
	$('.ilpa_delete_button').on('click', deleteRow);


	// Make the column headers "select all" and "select none" handlers
	if (header_row == 1) {
		this.getCheckboxesInColumn = function (column) {
			var re = new RegExp('attendance\\[.+\\]\\[' + column + '\\]');
			return $(".ilpa_column input[type=checkbox]").filter(function() {
				return this.name.match(re);
			});
		};
		var self = this;

		var $head_columns = $('th.ilpa_column');
		$head_columns.each(function () {

			var offset = $head_columns.first().index();
			$(this).click(function () {
				var $checkboxes = self.getCheckboxesInColumn($(this).index() - offset);
				if ($checkboxes.length == $checkboxes.filter(':checked').length) {
					$checkboxes.removeAttr('checked');
				} else {
					$checkboxes.attr('checked', 'checked');
				}
			});
		})
	}
});