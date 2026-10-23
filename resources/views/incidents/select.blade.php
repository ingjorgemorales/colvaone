@php
    $selected = old($field.'_id', $incident->{$field.'_id'});
    if (!$incident->exists && $field === 'area' && !$selected) {
        $selected = $options->get('area', collect())->firstWhere('name', auth()->user()->area)?->id;
    }
@endphp
<div>
    <label for="{{ $field }}_id">{{ $label }} @if($required)<span class="incident-required">*</span>@endif</label>
    <select id="{{ $field }}_id" name="{{ $field }}_id" class="input-field" @required($required)>
        <option value="">Seleccionar...</option>
        @foreach($options->get($field, collect()) as $option)
            <option value="{{ $option->id }}" @selected($selected == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>
        @endforeach
    </select>
</div>
