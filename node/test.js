var ipFinder = require("./ip.js")

ipFinder.loadFile(__dirname+"/ipdatacloud.dat")

console.log(ipFinder.get("180.101.49.11"))
