jQuery(document).ready(function($){
	
	var url = "http://"+$(location).attr('host')+"/wp-content/plugins/twitter/getTwitters.php";
	var twitter_block = $("#twitter_block");
	var twitter_logo = $("#twitter_logo");
	var twitter_entries = $("#twitter_entries");
	var twitter_refresh_link = $("#twitter_refresh_link");
	
	twitter_refresh_link.click(function(e){
		e.preventDefault();
		getTweets();
	});
	
	twitter_entries.corner("8px");
	getTweets();
	function getTweets(){
		$.ajax({
			url	: url,
			dataType: "json",
			beforeSend: function(){
				twitter_entries.html("<span class='twitter_loading'>Loading Tweets...<span>");		 
			},
			success: function(response){
				var entries = "";
				var date = "";
				var avatar = "";
				$.each(response, function(i, tweet){
					avatar = tweet.user.profile_image_url;
					date = relative_created_at(tweet.created_at);
					permalink = "http://twitter.com/di0fref/statuses/"+tweet.id_str;
					entries += "<div class='twitter_entry'><div class='twitter_date clear'><a href='"+permalink+"' title='Permalink to this tweet'>"+date+"</a></div><div class='twitter_avatar'><img src='"+avatar+"'/></div><div class='twitter_text'><p>"+autolink(tweet.text)+"</p></div></div>";
				
				});
					
				twitter_entries.html(entries);
			
			},
			error: function(error){
				twitter_entries.html("<b>Error:</b> Could not load Tweets." + error)
			}
		});
	}
	
	function relative_created_at(time_value) {  
	     var created_at_time = Date.parse(time_value.replace(" +0000",""));
	     var relative_time = ( arguments.length > 1 ) ? arguments[1] : new Date();
	     var wordy_time = parseInt(( relative_time.getTime() - created_at_time ) / 1000) + (relative_time.getTimezoneOffset()*60);
	     var returnString;

	    if ( wordy_time < 59 ) {
			returnString = 'less than a minute ago';
		} 
		else if ( wordy_time < 119 ) {       // changed because otherwise you get 30 seconds of 1 minutes ago  
			returnString = 'about a minute ago';
		} 
		else if ( wordy_time < 3000 ) {         // < 50 minutes ago
			returnString = ( parseInt( wordy_time / 60 )).toString() + ' minutes ago';
		} 
		else if ( wordy_time < 5340 ) {         // < 89 minutes ago
			returnString = 'about an hour ago';
		} 
		else if ( wordy_time < 9000 ) {          // < 150 minutes ago
			returnString = 'a couple of hours ago';  
		}
		else if ( wordy_time < 82800 ) {         // < 23 hours ago
			returnString = 'about ' + ( parseInt( wordy_time / 3600 )).toString() + ' hours ago';
		} 
		else if ( wordy_time < 129600 ) {       //  < 36 hours
			returnString = 'a day ago';
		}
		else if ( wordy_time < 172800 ) {       // < 48 hours
			returnString = 'almost 2 days ago';
		}
		else {
			returnString = ( parseInt(wordy_time / 86400)).toString() + ' days ago';
		}
			return returnString;
	}
	
	function autolink(s) {   
		var hlink = /(ht|f)tp:\/\/([^ \,\;\:\!\)\(\"\'\<\>\f\n\r\t\v])+/g;
		return (s.replace (hlink, function ($0,$1,$2) { 
			s = $0.substring(0,$0.length); 
	        while (s.length>0 && s.charAt(s.length-1)=='.' || s.charAt(s.length-1)==')' || s.charAt(s.length-1)==']'  || s.charAt(s.length-1)=='"' || s.charAt(s.length-1)=='}' || s.charAt(s.length-1)==',' ) 
				s = s.substring(0,s.length-1);
				return " " + s.link(s);   
	    	}) 
	    );
	}	
});