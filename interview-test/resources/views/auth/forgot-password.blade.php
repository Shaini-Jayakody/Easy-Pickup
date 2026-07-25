<x-guest-layout>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="list-unstyled">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Email Password Reset Link</button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </form>
</x-guest-layout>