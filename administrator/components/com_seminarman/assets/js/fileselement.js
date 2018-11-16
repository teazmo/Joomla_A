function qffileselementadd(id, file) {
	document.adminForm.file.value=id;	
	window.parent.qfSelectFile(id, file);	
	document.adminForm.submit();	
}

function qfcourseimageadd(id, file, url) {
	var new_img = url + file;
	window.parent.document.getElementById("courseimg").src = new_img;
	window.parent.document.getElementById("image").value = file;
	document.adminForm.file.value=id;	
	// window.parent.qfSelectFile(id, file);	
	window.parent.SqueezeBox.close();	
}