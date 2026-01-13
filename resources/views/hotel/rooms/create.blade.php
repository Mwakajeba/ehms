@extends('layouts.main')

@section('title', 'Add New Room')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Hotel & Property Management', 'url' => route('hotel.management.index'), 'icon' => 'bx bx-building-house'],
            ['label' => 'Room Management', 'url' => route('rooms.index'), 'icon' => 'bx bx-bed'],
            ['label' => 'Add New Room', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add New Room</h4>
                        <p class="card-subtitle text-muted">Create a new room in your hotel</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('rooms.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Property <span class="text-danger">*</span></label>
                                        <select name="property_id" class="form-select @error('property_id') is-invalid @enderror">
                                            <option value="">Select Property</option>
                                            @foreach($properties as $property)
                                                <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                                    {{ $property->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('property_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Room Number <span class="text-danger">*</span></label>
                                        <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" placeholder="e.g., 101, A-201" value="{{ old('room_number') }}">
                                        @error('room_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                        <select name="room_type" class="form-select @error('room_type') is-invalid @enderror">
                                            <option value="">Select Room Type</option>
                                            <option value="single" {{ old('room_type') == 'single' ? 'selected' : '' }}>Single Room</option>
                                            <option value="double" {{ old('room_type') == 'double' ? 'selected' : '' }}>Double Room</option>
                                            <option value="twin" {{ old('room_type') == 'twin' ? 'selected' : '' }}>Twin Room</option>
                                            <option value="suite" {{ old('room_type') == 'suite' ? 'selected' : '' }}>Suite</option>
                                            <option value="deluxe" {{ old('room_type') == 'deluxe' ? 'selected' : '' }}>Deluxe Room</option>
                                        </select>
                                        @error('room_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Rate per Night (TSh) <span class="text-danger">*</span></label>
                                        <input type="number" name="rate_per_night" class="form-control @error('rate_per_night') is-invalid @enderror" placeholder="0" min="0" value="{{ old('rate_per_night') }}">
                                        @error('rate_per_night')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Rate per Month (TSh)</label>
                                        <input type="number" name="rate_per_month" class="form-control @error('rate_per_month') is-invalid @enderror" placeholder="0" min="0" value="{{ old('rate_per_month') }}">
                                        @error('rate_per_month')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" placeholder="2" min="1" max="10" value="{{ old('capacity', 2) }}">
                                        @error('capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Floor</label>
                                        <input type="number" name="floor_number" class="form-control @error('floor_number') is-invalid @enderror" placeholder="e.g., 1, 2, 3" value="{{ old('floor_number') }}" min="0">
                                        @error('floor_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                            <option value="out_of_order" {{ old('status') == 'out_of_order' ? 'selected' : '' }}>Out of Order</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Room description, amenities, etc.">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Amenities</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="wifi" id="wifi" {{ in_array('wifi', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="wifi">WiFi</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="ac" id="ac" {{ in_array('ac', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ac">Air Conditioning</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="tv" id="tv" {{ in_array('tv', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tv">TV</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="minibar" id="minibar" {{ in_array('minibar', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="minibar">Minibar</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Additional Features</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="balcony" id="balcony" {{ in_array('balcony', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="balcony">Balcony</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="ocean_view" id="ocean_view" {{ in_array('ocean_view', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ocean_view">Ocean View</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="city_view" id="city_view" {{ in_array('city_view', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="city_view">City View</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="smoking" id="smoking" {{ in_array('smoking', old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="smoking">Smoking Allowed</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Room
                                </button>
                                <a href="{{ route('rooms.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
