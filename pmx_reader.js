
function readPMX(buffer) {
    var reader = new PMXStructures(buffer);
}

function bin2ascii(array) {
    let s = '';
    for (var i in array) {
        s += String.fromCodePoint(array[i]);
    }
    return s;
}

function Vec2(x, y) {
    this.X = x;
    this.Y = y;
}

function Vec3(x, y, z) {
    this.X = x;
    this.Y = y;
    this.Z = z;
}

function Vec4(x, y, z, a) {
    this.X = x;
    this.Y = y;
    this.Z = z;
    this.A = a;
}

function Vertex(pv, nv, uv) {
    this.Position = pv;
    this.Vormal = nv;
    this.UV = uv;
    // this.addtionalVec4 = av4;
}

function checkSignature(buffer) {
    return buffer.getString(4, 0) === 'PMX ';
}

function PMXStructures(buffer) {
    this.reader = new BinaryReader(buffer);

    this.currentBufferOffset = 0;

    this.version = this.reader.getFloat32(4);
    this.globalCount = this.reader.getBytes(8, 1)[0];

    this.globalHeaders = [0, 0, 0, 0, 0, 0, 0, 0];
    for(var i = 0; i < this.globalCount; i++) {
        this.globalHeaders[i] = this.reader.getBytes(9 + i, 1)[0];
    }
    console.log(this.globalHeaders);

    this.readNameHeaders();
    this.readEachCount();

    console.log([
        this.nameJp,
        this.nameEn,
        this.commentJp,
        this.commentEn
    ]);
}

PMXStructures.prototype.readText = function (offset, le) {
    var bytes = this.reader.getUInt32(offset, true);

    var read = this.reader.getBytes(offset + 4, bytes);

    return {readBytes: 4 + bytes, text: ArrayToUtf16String(read, true)};
}

function ArrayToUtf16String(arr, le) {
    var string = '';

    for (var i = 0; i < arr.length; i+= 2) {
        var data = (le) ? arr[i + 1] * 256 + arr[i]: arr[i] * 256 + arr[i + 1];

        string += String.fromCharCode(data);
    }
    return string;
}

PMXStructures.prototype.readNameHeaders = function() {
    var placeholder = null;

    var offset = 9 + this.globalCount;

    var readTexts = [];
    for (var i = 0; i < 4; i++) {
        placeholder = this.readText(offset, true);
        readTexts.push(placeholder.text);
        offset += placeholder.readBytes;
    }

    this.nameJp = readTexts[0];
    this.nameEn = readTexts[1];
    this.commentJp = readTexts[2];
    this.commentEn = readTexts[3];

    this.headerBytes = offset;
}

PMXStructures.prototype.readEachCount = function () {
    var offset = 0;
    var counts = [];

    var readEls = 9;
    if (this.version > 2) readEls = 10;

    handlers = [
        this.readVertices
    ];

    for(var i = 0; i < 1; i++) {
        console.log(this.headerBytes, this.reader.getBytes(this.headerBytes + offset, 4, true));
        var count = this.reader.getUInt32(this.headerBytes + offset, true);
        counts.push(count);
        this.readVertices(this.headerBytes + offset + 4, count);

        // offset += 4 + readBytes;
    }
    console.log(counts);
}

PMXStructures.prototype.readVertices = function (offset, count) {
    for (var i = 0; i < count; i++) {
        var x = this.reader.getFloat32(offset + 0, true);
        var y = this.reader.getFloat32(offset + 4, true);
        var z = this.reader.getFloat32(offset + 8, true);

        var nx = this.reader.getFloat32(offset + 12, true);
        var ny = this.reader.getFloat32(offset + 16, true);
        var nz = this.reader.getFloat32(offset + 20, true);

        var ux = this.reader.getFloat32(offset + 24, true);
        var uy = this.reader.getFloat32(offset + 28, true);

        var av4s = [];
        var av4s_offset = offset + 32;
        for (var i = 0; i < this.globalHeaders[1]; i++) {
            var ax = this.reader.getFloat32(av4s_offset + 0, true);
            var ay = this.reader.getFloat32(av4s_offset + 4, true);
            var az = this.reader.getFloat32(av4s_offset + 8, true);
            var aa = this.reader.getFloat32(av4s_offset + 12, true);

            av4s.push(new Vec4(ax, ay, az, aa));
            av4s_offset += 16;
        }

        var defType = this.reader.getBytes(av4s_offset, 1)[0];

        console.log(new Vertex(
            new Vec3(x, y, z),
            new Vec3(nx, ny, nz),
            new Vec2(ux, uy)), av4s);

        break;
    }
}