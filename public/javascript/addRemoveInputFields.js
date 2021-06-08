$(document).ready(function(){
	$(".form-group").on('click', ".add-more", function(e){
		e.preventDefault();
		
		var oldInputGroupID = e.target.id.replace("add_", "" );
		var escapedOldInputGroupID = "#"+oldInputGroupID.replace(/\./g, "\\." );
		var partsOfClickedId = oldInputGroupID.split("_");
		var clickedIdNumber = parseInt(partsOfClickedId.pop());
		var fieldOfTargetedId = partsOfClickedId.join("_");
		var escapedFieldOfTargetedId = fieldOfTargetedId.replace(/\./g, "\\." );
		
		$(escapedOldInputGroupID).nextAll(`[id^=${escapedFieldOfTargetedId}]`).each(function(){
			var partsOfId = this.id.split("_");
			var place = parseInt(partsOfId.pop());
			var field = partsOfId.join("_");			
			this.id = `${field}_${place+1}`;	
		});
		
		$(escapedOldInputGroupID).nextAll(`[id^=${escapedFieldOfTargetedId}]`).each(function(){
			var partsOfId = this.id.split("_");
			var place = parseInt(partsOfId.pop());
			var field = partsOfId.join("_");
			var escapedID = "#"+this.id.replace(/\./g, "\\." );
			$(escapedID).children().each(function(){
				if (this.id) {
					var partsOfId = this.id.split("_");
					var number = parseInt(partsOfId.pop());
					var field = partsOfId.join("_");
					this.id = `${field}_${number+1}`;
				}
				if (this.name) {
					if(this.tagName === "INPUT") {
						var partsOfName = this.name.split("][");
						partsOfName[2] = parseInt(partsOfName[2])+1;
						this.name = partsOfName.join("][");
						this.name += "]";
					}
					else {
						var partsOfName = this.name.split("][");
						if(Number.isInteger(partsOfName[1]))
						{
							partsOfName[1] = parseInt(partsOfName[1])+1;
						}
						else
						{
							partsOfName[1] = parseInt(partsOfName[1])+1;
						}
						this.name = partsOfName.join("][");
						this.name += "]";
					}
				}				
			});			
		});
		
		var newInputGroupID = `${fieldOfTargetedId}_${clickedIdNumber+1}`;
		var escapedNewInputGroupID = "#"+newInputGroupID.replace(/\./g, "\\.");

		var newInput = $(escapedOldInputGroupID).clone(false);
		
		$("textarea", newInput).text("");
		
		$("*", newInput).add(newInput).each(function() {
			if (this.id) {
				this.id = this.id.replace(oldInputGroupID,newInputGroupID);
			}
			if (this.name) {
				this.name = this.name.replace(clickedIdNumber,`${clickedIdNumber+1}`);
			}
		});
        
        $(escapedOldInputGroupID).after(newInput);
	});
	
	$(".form-group").on('click', ".remove-me", function(e){
		e.preventDefault();
		
		var inputGroupID = e.target.id.replace("remove_", "" );
		
		var escapedInputGroupID = "#"+inputGroupID.replace(/\./g, "\\." );
		
		$(escapedInputGroupID).remove();
		
		var partsOfClickedId = inputGroupID.split("_");
		var clickedIdNumber = parseInt(partsOfClickedId.pop());
		var fieldOfTargetedId = partsOfClickedId.join("_");
		var escapedFieldOfTargetedId = fieldOfTargetedId.replace(/\./g, "\\." );
		var previousInputGroupID = `${fieldOfTargetedId}_${clickedIdNumber-1}`;
		var escapedPreviousInputGroupID = "#"+previousInputGroupID.replace(/\./g, "\\." );
		
		$(escapedPreviousInputGroupID).nextAll(`[id^=${escapedFieldOfTargetedId}]`).each(function(){
			var partsOfId = this.id.split("_");
			var place = parseInt(partsOfId.pop());
			var field = partsOfId.join("_");
			this.id = `${field}_${place-1}`;
			var escapedID = "#"+this.id.replace(/\./g, "\\." );
			$(escapedID).children().each(function(){
				if (this.id) {
					var partsOfId = this.id.split("_");
					var number = parseInt(partsOfId.pop());
					var field = partsOfId.join("_");
					this.id = `${field}_${number-1}`;
				}
				if (this.name) {
					if(this.tagName === "INPUT") {
						var partsOfName = this.name.split("][");
						partsOfName[2] = parseInt(partsOfName[2])-1;
						this.name = partsOfName.join("][");
						this.name += "]";
					}
					else {
						var partsOfName = this.name.split("][");
						partsOfName[1] = parseInt(partsOfName[1])-1;
						this.name = partsOfName.join("][");
						this.name += "]";
					}
				}				
			});
		});
	});
});