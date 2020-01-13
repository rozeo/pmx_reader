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
    
    let lookAt = new THREE.Vector3(0, 15, 0);
    // 注視点
    camera.lookAt(lookAt);

    let light = new THREE.DirectionalLight(0xFFFFFFFF);
    light.position.set(1, 1, 1);

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
    let prev = null;
    let crQuate = null;
    canvas.addEventListener('mousedown', function (evt) {
        clicked = true;
        prev = evt.pageX;
    });

    canvas.addEventListener('mousemove', function (evt) {
        if(clicked) {
            let quate = new THREE.Quaternion();
            var axis = new THREE.Vector3(0, 1, 0);
            quate.setFromAxisAngle(axis, (prev < evt.pageX) ? -(Math.PI / 180 * 2): (Math.PI / 180 * 2));
            crQuate = quate;

            let pos = new THREE.Vector3(camera.position.x, camera.position.y, camera.position.z);
            let look = new THREE.Vector3(camera.lookAt.x, camera.lookAt.y, camera.lookAt.z);

            pos.applyQuaternion(quate);
            camera.position.x = pos.x;
            camera.position.y = pos.y;
            camera.position.z = pos.z;

            camera.lookAt(lookAt);
            prev = evt.pageX;
        }
    });

    canvas.addEventListener('mouseup', function (evt) {
        clicked = false;
    });


    window.addEventListener('wheel', function(evt) {
        let np = new THREE.Vector3(camera.position.x, camera.position.y, camera.position.z);
        np = np.normalize();
        if (evt.deltaY < 0) {
            camera.position.x += np.x * 2;
            // camera.position.y += np.y * 2;
            camera.position.z += np.z * 2;
        } else {
            camera.position.x += np.x * -2;
            // camera.position.y += np.y * -2;
            camera.position.z += np.z * -2;
        }
        camera.lookAt(lookAt);
    });

    
    scene.add(light);

    let clock = new THREE.Clock(true);

    function update() {
        var delta = clock.getDelta();
        animate( delta );
    
        renderer.render(scene, camera);
        if (physics !== null) {
            physics.update();
        }
        requestAnimationFrame(update);
    }

    let loader = new THREE.MMDLoader();
    let physics = null;

    loader.load(
        'http://localhost/miku/model.pmx',
        function (mesh) {
            physics = mesh[1];
            scene.add(mesh[0]);
        }, function (){}, function(){}
    );

    update();
});
