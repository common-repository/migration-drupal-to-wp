window.onload = function() {
  var term=document.getElementById("term").addEventListener("click", mostrar);

  function mostrar(){
  	if(document.getElementById("ocult").style.display=='none'){
  		document.getElementById("ocult").style.display = 'block';
  	} else {
  		document.getElementById("ocult").style.display='none'
  	}	
  }
};