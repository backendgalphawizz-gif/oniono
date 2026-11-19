<script>
    (function () {
        var stateSelect = document.querySelector('[data-register-state-select]');
        var citySelect = document.querySelector('[data-register-city-select]');
        if (!stateSelect || !citySelect) {
            return;
        }

        var citiesUrl = citySelect.getAttribute('data-cities-url');
        var initialCityId = citySelect.value;

        function setCityOptions(cities, selectedId) {
            citySelect.innerHTML = '';
            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select city…';
            citySelect.appendChild(placeholder);

            cities.forEach(function (city) {
                var opt = document.createElement('option');
                opt.value = String(city.id);
                opt.textContent = city.name;
                if (selectedId && String(selectedId) === String(city.id)) {
                    opt.selected = true;
                }
                citySelect.appendChild(opt);
            });

            citySelect.disabled = cities.length === 0;
        }

        stateSelect.addEventListener('change', function () {
            var stateId = stateSelect.value;
            if (!stateId) {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Select state first</option>';
                return;
            }

            citySelect.disabled = true;
            citySelect.innerHTML = '<option value="">Loading cities…</option>';

            fetch(citiesUrl + '?state_id=' + encodeURIComponent(stateId), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    setCityOptions(payload.data || [], null);
                })
                .catch(function () {
                    citySelect.innerHTML = '<option value="">Could not load cities</option>';
                });
        });

        if (initialCityId && stateSelect.value) {
            setCityOptions(
                Array.from(citySelect.options)
                    .filter(function (o) { return o.value !== ''; })
                    .map(function (o) { return { id: o.value, name: o.textContent }; }),
                initialCityId
            );
        }
    })();
</script>
