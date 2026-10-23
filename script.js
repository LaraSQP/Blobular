$(document).ready(async function () {
	const response = await fetch('blob-config.json', { cache: 'no-store' });

	if (!response.ok) {
		console.error('Could not load blob-config.json');
		return;
	}

	const config = await response.json();

	const { blobColor, upperLight, lowerLight, ambientLight, speed, spikes, processing } = config;

	// The renderer
	const canvas = $('#blob canvas')[0];

	const renderer = new THREE.WebGLRenderer({
		canvas: canvas,
		antialias: true,
		alpha: true,
	});

	renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

	const scene  = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 1000);

	camera.position.z = 5;

	const geometry         = new THREE.SphereGeometry(0.8, 128, 128);
	const originalVertices = geometry.vertices.map((v) => v.clone());

	const material = new THREE.MeshPhongMaterial({ color: blobColor, shininess: 100 });

	const lightTop = new THREE.DirectionalLight(upperLight, 0.7);

	lightTop.position.set(0, 500, 200);
	scene.add(lightTop);

	const lightBottom = new THREE.DirectionalLight(lowerLight, 0.25);

	lightBottom.position.set(0, -500, 400);
	scene.add(lightBottom);

	scene.add(new THREE.AmbientLight(ambientLight));

	const sphere = new THREE.Mesh(geometry, material);

	scene.add(sphere);

	const simplex = new SimplexNoise();

	function resize() {
		const rect = canvas.getBoundingClientRect();
		if (rect.width <= 0 || rect.height <= 0) return;

		renderer.setSize(rect.width, rect.height, false);
		camera.aspect = rect.width / rect.height;
		camera.updateProjectionMatrix();
	}

	function update(now) {
		const time        = now * 0.001 * speed * Math.pow(processing, 3);
		const spikeAmount = spikes * processing;

		for (let i = 0; i < originalVertices.length; i++) {
			const p = originalVertices[i];
			const noise = simplex.noise3D(
				p.x * spikeAmount,
				p.y * spikeAmount,
				p.z * spikeAmount + time
			);

			geometry.vertices[i]
				.copy(p)
				.normalize()
				.multiplyScalar(1 + 0.3 * noise);
		}

		geometry.computeVertexNormals();
		geometry.normalsNeedUpdate  = true;
		geometry.verticesNeedUpdate = true;
	}

	function animate(now) {
		update(now);
		renderer.render(scene, camera);
		requestAnimationFrame(animate);
	}

	window.addEventListener('resize', resize);
	resize();
	requestAnimationFrame(animate);
});
