window.addEventListener('DOMContentLoaded', function() {
    let canvas = document.createElement('canvas');

    document.getElementsByTagName('body')[0].appendChild(canvas);

    const renderer = new THREE.WebGLRenderer({ canvas: canvas });

    renderer.setSize(window.innerWidth, window.innerHeight, false);
    renderer.setPixelRatio(window.devicePixelRatio);

    const scene = new THREE.Scene();

    scene.background = new THREE.Color(0xFFFFFFFF);

    const camera = new THREE.PerspectiveCamera(
        45, window.innerWidth / window.innerHeight, 1, 100000.0
    );

    camera.position.set(0, 15, 20);
    let cameraLot = 0;
    
    let camUp = new THREE.Vector3(0, 1, 0);
    let lookAt = new THREE.Vector3(0, 15, 0);
    // 注視点
    camera.lookAt(lookAt);
    camera.up = camUp;

    
    let light = new THREE.DirectionalLight(0xFFFFFFFF);
    light.position.set(1, 1, 1);

    /*
    let xLineGeo = new THREE.BoxGeometry(2, 0.1, 0.1);
    let yLineGeo = new THREE.BoxGeometry(0.1, 2, 0.1);
    let zLineGeo = new THREE.BoxGeometry(0.1, 0.1, 2);

    let xLine = new THREE.Mesh(xLineGeo, new THREE.MeshToonMaterial({ color: 0x00FF0000, linewidth: 3 }));
    let yLine = new THREE.Mesh(yLineGeo, new THREE.MeshToonMaterial({ color: 0x0000FF00, linewidth: 3 }));
    let zLine = new THREE.Mesh(zLineGeo, new THREE.MeshToonMaterial({ color: 0x000000FF, linewidth: 3 }));

    scene.add(xLine);
    scene.add(yLine);
    scene.add(zLine);
    */

    // create horizontal
    (function(scene) {
        let material = new THREE.LineBasicMaterial({ color: 0xFF000000 });

        for (let x = 0; x < 20; x++) {
            let xMov = 10 * (x - 10);

            for (let y = 0; y < 20; y++) {
                let geometry = new THREE.PlaneGeometry(1, 1);
            
                let yMov = 10 * (y - 10);
                
                geometry.vertices = [
                    new THREE.Vector3(xMov, 0, yMov),
                    new THREE.Vector3(xMov + 10, 0, yMov),
                    new THREE.Vector3(xMov, 0, yMov + 10),
                    new THREE.Vector3(xMov + 10, 0, yMov + 10)
                ];

                let edge = new THREE.EdgesGeometry(geometry);

                scene.add(new THREE.LineSegments(edge, material));
            }
        }
    })(scene);

    let clicked = false;
    let prevX = null;
    let prevY = null;
    let crQuate = null;
    canvas.addEventListener('mousedown', function (evt) {
        clicked = true;
        prevX = evt.pageX;
        prevY = evt.pageY;
    });

    let axisY = new THREE.Vector3(1, 0, 0);
    canvas.addEventListener('mousemove', function (evt) {
        if(clicked) {
            subX = evt.pageX - prevX;
            subY = evt.pageY - prevY;

            let quateX = new THREE.Quaternion();
            let quateY = new THREE.Quaternion();

            let axisX = new THREE.Vector3(0, 1, 0);

            let rotateAngleX = Math.PI / 180 * 2 * subX * -0.1;
            let rotateAngleY = Math.PI / 180 * 2 * subY * -0.1;

            quateX.setFromAxisAngle(axisX, rotateAngleX);
            quateY.setFromAxisAngle(axisY, rotateAngleY);
            // crQuate = quate;

            let orgVec = camera.position.clone();
            orgVec.add(lookAt.clone().negate());

            let vVec = orgVec.clone();
            let hVec = orgVec.clone();

            vVec.applyQuaternion(quateX);
            hVec.applyQuaternion(quateY);
            axisY.applyQuaternion(quateX);
            
            /* 正規化 */
            vVec.add(orgVec.clone().negate());
            hVec.add(orgVec.clone().negate());
            vVec.add(hVec);

            camera.position.add(vVec);

            camUp.applyQuaternion(quateY);

            camera.lookAt(lookAt);

            prevX = evt.pageX;
            prevY = evt.pageY;
        }
    });

    canvas.addEventListener('mouseup', function (evt) {
        clicked = false;
    });


    window.addEventListener('wheel', function(evt) {
        let np = new THREE.Vector3(camera.position.x - lookAt.x, camera.position.y - lookAt.y, camera.position.z - lookAt.z);
        np = np.normalize();
        if (evt.deltaY < 0) {
            camera.position.x -= np.x;
            camera.position.y -= np.y;
            camera.position.z -= np.z;
        } else {
            camera.position.x += np.x;
            camera.position.y += np.y;
            camera.position.z += np.z;
        }
    });

    
    scene.add(light);

    let clock = new THREE.Clock(true);

    function update() {
        var delta = clock.getDelta();
    
        renderer.render(scene, camera);
        if (physics !== null) {
            physics.update();
        }
        requestAnimationFrame(update);
    }

    let loader = new THREE.MMDLoader();
    let physics = null;

    /*loader.load(
        'http://localhost/miku/model.pmx',
        function (mesh) {
            physics = mesh[1];
            scene.add(mesh[0]);
        }, function (){}, function(){}
    );*/

    update();
});
