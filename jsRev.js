// 'notes' to store Arrows  
var notes = [];


// ==== CLASS FOR ARROWS ==== //

// 1. Direction of arrows
// 2. jQuery img that links to direction bottom
// 3. Destroy when it arrow gets to the 
// 4. Explode when arrow gets to the bottom

// Class Arrow
function Arrow(direction) {

	// CSS spacings for the arrows //
	var xPos = null;

	switch(direction) {

		case "left" : xPos = "115px";
		break;

		case "up" : xPos = "182";
		break;

		case "down" : xPos = "252px";
		break;

		case "right" : xPos = "322px";
		break;

	}

	this.direction = direction;
	this.image = $("<img src='./arrows/" + direction + ".gif'/>");
	this.image.css({

		position: "absolute",
		top: "0px",
		left: xPos

	});

	$('.stage').append(this.image);

}// ends CLASS Arrow

// To enable animating the arrows
Arrow.prototype.step = function() {

	// Controls the speed of the arrows
	this.image.css("top", "+=4px");

};

// Deletes arrows when they get to bottom of page
Arrow.prototype.destroy = function() {

	// removes the image of the DOM
	this.image.remove();

	// Removes the note/arrow from memory/array
	notes.splice(0,1);

};

// Explodes arrow when hit
Arrow.prototype.explode = function() {

	this.image.remove();

};

//Number of steps missed
var numMissed = 0;

//Score variable
var score = 0;

// For random arrows
var randNum = 0;

// Frame increasing
var frame = 0;

// Determines the speed of notes
var arrowSpawnRate = 40;

// Game state
var isPlaying = true;


// Random generator for arrows
function randomGen() {

	// Randomizes between 1 and 4
	randNum = Math.floor(Math.random() * 4) + 1;

	if (randNum === 1) {

		notes.push(new Arrow("left"));

	}
	if (randNum === 2) {

		notes.push(new Arrow("right"));

	}
	if (randNum === 3) {

		notes.push(new Arrow("up"));
		
	}
	if (randNum === 4) {

		notes.push(new Arrow("down"));

	}

}// ends randomGen()


// Render function //
function render() {

	if(isPlaying == true) {

		//Hide After game elements
		document.getElementById("winner").style.display = "none";
		document.getElementById("replayButton").style.display = "none";
		document.getElementById("endScore").style.display = "none";

		//Show controls
		document.getElementById("controls").style.visibility = "visible";

		//Show Stage
		document.getElementById("stage").style.visibility = "visible";

		if (frame++ % arrowSpawnRate === 0) {

			randomGen();

		}

		// Animate arrows showering down //
		for (var i = notes.length - 1; i >= 0; i--) {

			notes[i].step();

			// Check for cleanup
			if (notes[i].image.position().top > 615) {
				if (numMissed < 9 && numMissed > -1) {
					numMissed++;
				}
				notes[i].destroy();
				
			}

		}

		//Update score display
		document.getElementById("score").innerHTML = "Score: " + score;

		//Increase speed when score increases
		if(score > 150) 
			arrowSpawnRate = 30;
		if(score > 300)
			arrowSpawnRate = 25;
		if(score > 500)
			arrowSpawnRate = 20;
		if(score > 750)
			arrowSpawnRate = 10;

		//Update bar color and size
		switch(numMissed) {
			case 0:
				document.getElementById("bar").className = "bar green";
				document.getElementById("bar").style.marginTop = "0%";
				document.getElementById("bar").style.height ="100%";
				break;
			case 1:
				document.getElementById("bar").style.marginTop = "10%";
				document.getElementById("bar").style.height ="88%";
				break;
			case 2:
				document.getElementById("bar").style.marginTop = "19%";
				document.getElementById("bar").style.height ="78%";
				break;
			case 3:
				document.getElementById("bar").className = "bar green";
				document.getElementById("bar").style.marginTop = "31%";
				document.getElementById("bar").style.height ="65%";
				break;		
			case 4:
				document.getElementById("bar").className = "bar orange";
				document.getElementById("bar").style.marginTop = "40%";
				document.getElementById("bar").style.height ="54%";
				break;
			case 5:
				document.getElementById("bar").style.marginTop = "49%";
				document.getElementById("bar").style.height ="44%";
				break;
			case 6:
				document.getElementById("bar").className = "bar orange";
				document.getElementById("bar").style.marginTop = "58%";
				document.getElementById("bar").style.height ="34%";
				break;
			case 7:
				document.getElementById("bar").className = "bar red";
				document.getElementById("bar").style.marginTop = "66%";
				document.getElementById("bar").style.height ="25%";
				break;
			case 8:
				document.getElementById("bar").style.marginTop = "75%";
				document.getElementById("bar").style.height ="15%";
				break;
			case 9:
				document.getElementById("bar").style.height ="0%";
				isPlaying = false;
				break;
		}
	}
	//End game logic
	else {
		// Destroy all active notes //
		for (var i = notes.length - 1; i >= 0; i--) {
			notes[i].step();
			if (notes[i].image.position().top > 615)
				notes[i].destroy();
		}

		//Hide controls
		document.getElementById("controls").style.visibility = "hidden";

		//Hide Stage
		document.getElementById("stage").style.visibility = "hidden";

		//Show After Game
		var endScore = score;
		document.getElementById("winner").style.display = "block";
		document.getElementById("replayButton").style.display = "inline";
		document.getElementById("endScore").innerHTML = "Score: " + score;
		document.getElementById("endScore").style.display = "inline";
	}
}// ends render()

function toggleIsPlaying(choice) {
	isPlaying = choice;
	numMissed = 0;
	score = 0;
}

// jQuery to animate arrows //
$(document).ready(function () {

	// shim layer with setTimeout fallback
	window.requestAnimFrame = (function() {

		return window.requestAnimationFrame ||

		window.webkitRequestAnimationFrame ||

		window.mozRequestAnimationFrame ||

		function(callback) {

			window.setTimeout(callback, 40 / 75);

		};

	})();

	/*	place the rAF *before* the render() 
		to assure as close to 60fps with the 
		setTimeout fallback.					*/

	// Infinte loop for game play
	(function animloop() {

		requestAnimFrame(animloop);

		render();

	})();// ends (function animloop() )


});// ends $(doc).ready



// Listening for when the key is pressed
$(document).keydown( function(event) {
	var hit = false;
	for (var i = 0; i < notes.length; i++) {
	
			console.log(notes[i].image.position().top);

		if (event.keyCode == 37 && notes[i].direction == "left") {

			if (notes[i].image.position().top > 490 && notes[i].image.position().top < 530) {

				console.log("LEFT! "+notes[i].explode());
				score += 10;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}
			else if (notes[i].image.position().top > 480 && notes[i].image.position().top < 540) {

				console.log("LEFT! "+notes[i].explode());
				score += 5;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}
			
		}
		if (event.keyCode == 38 && notes[i].direction == "up") {

			if (notes[i].image.position().top > 490 && notes[i].image.position().top < 530) {
				
				console.log("UP! "+notes[i].explode());
				score += 10;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}
			else if (notes[i].image.position().top > 480 && notes[i].image.position().top < 540) {

				console.log("UP! "+notes[i].explode());
				score += 5;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}

		}
		if (event.keyCode == 40 && notes[i].direction == "down") {

			if (notes[i].image.position().top > 490 && notes[i].image.position().top < 530) {
				
				console.log("DOWN! "+notes[i].explode());
				score += 10;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}
			else if (notes[i].image.position().top > 480 && notes[i].image.position().top < 540) {

				console.log("DOWN! "+notes[i].explode());
				score += 5;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}

		}
		if (event.keyCode == 39 && notes[i].direction == "right") {

			if (notes[i].image.position().top > 490 && notes[i].image.position().top < 530) {
				
				console.log("RIGHT! "+notes[i].explode());
				score += 10;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}
			else if (notes[i].image.position().top > 480 && notes[i].image.position().top < 540) {

				console.log("RIGHT! "+notes[i].explode());
				score += 5;
				if(numMissed < 9 && numMissed > 0) {
					numMissed--;
				}
				hit = true;
			}

		}

	}// ends loop
	
	//None of the correct keys were hit, mark as a miss
	if(hit == false)
		numMissed += 1;
	
});// ends $(doc).keyup
