@php
    $churchOptions = $churches->map(fn ($church) => [
        'id' => $church->id,
        'name' => $church->name,
        'circuitId' => $church->circuit_id,
        'circuit' => $church->circuit->name,
    ])->values();
    $circuitOptions = $churches->pluck('circuit')->unique('id')->sortBy('name')->values();
    $selectedChurch = $churches->firstWhere('id', (int) $selectedChurchId);
@endphp

<div class="col-12">
    <label class="form-label" for="church_search">Find a church</label>
    <div class="duyog-search-field">
        <i class="bx bx-search" aria-hidden="true"></i>
        <input class="form-control" id="church_search" type="search" placeholder="Type a church or Circuit name" autocomplete="off" aria-controls="church_search_results" aria-describedby="church_search_hint">
    </div>
    <div class="form-text" id="church_search_hint">Pick a result to fill in the Circuit and Local Church below, or choose them yourself.</div>
    <div class="list-group duyog-church-results" id="church_search_results" hidden></div>
</div>
<div class="col-md-6">
    <label class="form-label" for="circuit_filter">Circuit</label>
    <select class="form-select" id="circuit_filter">
        <option value="" @selected(! $selectedChurch)>Choose a Circuit</option>
        @foreach ($circuitOptions as $circuit)
            <option value="{{ $circuit->id }}" @selected($selectedChurch?->circuit_id === $circuit->id)>{{ $circuit->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-6">
    <label class="form-label" for="local_church_id">Local Church</label>
    <select class="form-select" id="local_church_id" name="local_church_id" required>
        <option value="" disabled @selected(! $selectedChurch)>Choose a church</option>
        @foreach ($churches->groupBy('circuit.name') as $circuitName => $circuitChurches)
            <optgroup label="{{ $circuitName }}">
                @foreach ($circuitChurches as $church)
                    <option value="{{ $church->id }}" @selected($selectedChurch?->id === $church->id)>{{ $church->name }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

<script>
    (() => {
        const churches = @json($churchOptions);
        const circuitSelect = document.getElementById('circuit_filter');
        const churchSelect = document.getElementById('local_church_id');
        const search = document.getElementById('church_search');
        const results = document.getElementById('church_search_results');

        const fillChurches = (circuitId, selectedId) => {
            churchSelect.innerHTML = '';

            const placeholder = new Option(circuitId ? 'Choose a church' : 'Choose a Circuit first', '', !selectedId, !selectedId);
            placeholder.disabled = true;
            churchSelect.add(placeholder);

            churches
                .filter((church) => String(church.circuitId) === String(circuitId))
                .forEach((church) => churchSelect.add(new Option(church.name, church.id, false, church.id === selectedId)));
        };

        const hideResults = () => {
            results.hidden = true;
            results.innerHTML = '';
        };

        const choose = (church) => {
            circuitSelect.value = String(church.circuitId);
            fillChurches(church.circuitId, church.id);
            search.value = '';
            hideResults();
            churchSelect.focus();
        };

        const showResults = () => {
            const query = search.value.trim().toLocaleLowerCase();
            results.innerHTML = '';

            if (query === '') {
                hideResults();
                return;
            }

            const matches = churches
                .filter((church) => church.name.toLocaleLowerCase().includes(query) || church.circuit.toLocaleLowerCase().includes(query))
                .slice(0, 8);

            if (matches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'list-group-item duyog-church-result-empty';
                empty.textContent = 'No church matches. Check the spelling, or choose the Circuit below.';
                results.appendChild(empty);
            }

            matches.forEach((church) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = '<strong></strong><span></span>';
                item.querySelector('strong').textContent = church.name;
                item.querySelector('span').textContent = church.circuit;
                item.addEventListener('click', () => choose(church));
                results.appendChild(item);
            });

            results.hidden = false;
        };

        fillChurches(circuitSelect.value, Number(churchSelect.value) || null);

        circuitSelect.addEventListener('change', () => {
            fillChurches(circuitSelect.value, null);
            if (circuitSelect.value) {
                churchSelect.focus();
            }
        });

        search.addEventListener('input', showResults);

        search.addEventListener('keydown', (event) => {
            const first = results.querySelector('button');

            if (event.key === 'Enter') {
                event.preventDefault();
                first?.click();
            } else if (event.key === 'ArrowDown' && first) {
                event.preventDefault();
                first.focus();
            } else if (event.key === 'Escape') {
                search.value = '';
                hideResults();
            }
        });

        results.addEventListener('keydown', (event) => {
            const items = [...results.querySelectorAll('button')];
            const index = items.indexOf(document.activeElement);

            if (event.key === 'ArrowDown' && index < items.length - 1) {
                event.preventDefault();
                items[index + 1].focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                (index > 0 ? items[index - 1] : search).focus();
            } else if (event.key === 'Escape') {
                hideResults();
                search.focus();
            }
        });
    })();
</script>
