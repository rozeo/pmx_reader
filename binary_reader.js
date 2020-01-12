// require Int8Array
function BinaryReader(buffer) {
    this.buffer = buffer;
}

BinaryReader.prototype.pow2 = function(n) {
	return (n >= 0 && n < 31) ? (1 << n) : (this.pow2[n] || (this.pow2[n] = Math.pow(2, n)));
}

BinaryReader.prototype.getBytes = function(offset, bytes, le) {
    var read = new Uint8Array(this.buffer.slice(offset, offset + bytes));

    return le && read.length > 1 ? read.reverse(): read;
}

BinaryReader.prototype.getFloat32 = function(offset, le) {
    var b = this.getUInt32(offset, le);

    var bits = [];
    var exponent = [];
    var fraction = [1];
    var num = 0;

    bits = b.toString(2);
    for(var i = bits.length; i < 32; i++) {
        bits = '0' + bits;
    }

    bits = bits.split('');
    bits.forEach(function (v, i) { bits[i] = parseInt(v, 10);});
    console.log(bits);

    sign = parseInt(bits.shift());

    for(var i = 0; i < 8; i++){
        exponent.push( bits.shift() );
    }
    
    for(var i=0; i < 23; i++){
        fraction.push( bits.shift() );
    };
    
    for(var i = 0, maxi = fraction.length; i < maxi; i++){
        if(fraction[i] == 1){
            num += parseFloat( 1 / Math.pow(2, i))
        }
    }
    
    num = num * Math.pow(2, (parseInt(exponent.join(""), 2) - 127));
    
    if(sign == 1){
        num *= -1
    }

    console.log(num);
    
    return num;
}

BinaryReader.prototype.getUInt32 = function(offset, le) {
    var bytes = this.getBytes(offset, 4, le);

    var result = 0;
    for (var i = 0; i < 4; i++) {
        result += bytes[i] * Math.pow(2, (4 - i - 1) * 8); 
    }
    return result;
}