(function() {
	// 配置野狗
	var config = {
		syncDomain: "yourWilddogID.wilddog.com",
		syncURL: "https://yourWilddogID.wilddogio.com"
	};
	wilddog.initializeApp(config);
	wilddog.sync().goOffline();
	wilddog.sync().goOnline();
	var ref = wilddog.sync().ref("reveal");
	// 客户端每次刷新都清除数据库
	ref.set(null);
	ref.on("value", function(snapshot, error) {
		if(snapshot.val() != null) {
			var backValue = snapshot.val();
			switch(backValue.status) {
				case 1:
					Reveal.up();
					break;
				case 2:
					Reveal.right();
					break;
				case 3:
					Reveal.down();
					break;
				case 4:
					Reveal.left();
					break;
				default:
			} 						
		}
	})

}())