"use strict";
var fs = require('fs');

var data = null;
var prefStart = [256];
var prefEnd = [256];
var endArr = [];
var addrArr = [];

var loadFile = function (filepath) {

	data = fs.readFileSync(filepath);
	var RecordSize = data.readUInt32LE(0);
	for (var k = 0; k < 256; k++) {
		var i = k * 8 + 4;
		prefStart[k] = data.readUInt32LE(i);
		prefEnd[k] = data.readUInt32LE(i + 4);
	}
	endArr = [RecordSize];
	addrArr = [RecordSize];
	for (var i = 0; i < RecordSize; i++) {
		var p = 2052 + (i * 9);
		endArr[i] = data.readUInt32LE(p);
		var offset = data.readUInt32LE(4 + p);
		var length = data.readUInt8(8 + p);//1 bit 无符号整型      
		addrArr[i] = data.slice(offset, offset + length).toString('utf-8');
	}


};


var Get = function (ip) {
	var ipArray = ip.split('.'), ipInt = ipToInt(ip), pref = parseInt(ipArray[0]);
	var low = prefStart[pref], high = prefEnd[pref];
	var cur = low == high ? low : Search(low, high, ipInt);
	if (cur==100000000){
		return null;		
	}
	return addrArr[cur];
}


var Search = function (low, high, k) {
	var M = 0;
	while (low <= high) {
		var mid = Math.floor((low + high) / 2);
		var endipNum = endArr[mid];
		if (endipNum >= k) {
			M = mid;
			if (mid === 0) {
				break;   //防止溢出
			}
			high = mid - 1;
		}
		else
			low = mid + 1;
	}
	return M
}

var ipToInt = function (ip) { return new Buffer(ip.split('.')).readUInt32BE(0) }

exports.loadFile = function (file) {
	if (data === null) {
		loadFile(file);
	}
}


exports.get = Get;
