var casper = require('casper').create({
    viewportSize: {
        width: 1920,
        height: 1080
    },
    pageSettings: {
        loadImages: false,
        loadPlugins: false,
    },
    verbose: true,
    logLevel: "debug"
});

var fs = require('fs'); // for saving files

var url 	= casper.cli.get(0);
var agent = casper.cli.get(1);
var id = casper.cli.get(6);
var today = casper.cli.get(7);
var content = '';

//url = 'http://bt.rozetka.com.ua/multivarki/c112986/filter/';
//agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0';
//id = 'multivarki#1';
//phantom.setProxy('213.219.244.175','7951','manual','rp3070736','aUzX1UIZbO');
/*
		var today = new Date();
		var dd = today.getDate();
		var mm = today.getMonth()+1; //January is 0!
		var yyyy = today.getFullYear();
		if(dd<10) {
		    dd = '0'+dd
		}
		if(mm<10) {
		    mm = '0'+mm
		} 
		today = dd + '.' + mm + '.' + yyyy;
*/

phantom.setProxy(casper.cli.get(2),casper.cli.get(3),'manual',casper.cli.get(4),casper.cli.get(5));
casper.options.waitTimeout = 40000;
casper.userAgent(agent);
casper.start();
casper.thenOpen(url);
casper.waitForSelector(".product-seller__title", function then() {
//casper.wait(200, function then() {
  var js = this.evaluate(function() {
		return document.querySelector("body").innerHTML;
	});	
    
		
		//casper.capture('/var/www/rozetka/screenshots/'+today+'/'+id+'.jpg');
		//casper.wait(1000);
		//if (js) {
			fs.write('/var/www/rozetka.ua/content/'+today+'/'+id+'.txt', js, 'w');
		//}
    
});

casper.run();
