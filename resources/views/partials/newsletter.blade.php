<!-- Newsletter -->
<section class="newsletter-section text-center">
    <div class="container">
        <h2 class="section-title mb-3">Join The Club</h2>
        <p class="text-muted mb-4 max-w-xl mx-auto">Subscribe to receive early access to new collections, exclusive campaign previews, and eco-initiatives updates.</p>
        <form action="{{ url('/newsletter/subscribe') }}" method="POST" class="newsletter-form d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2">
            @csrf
            <input type="email" name="email" class="form-control" placeholder="ENTER YOUR EMAIL ADDRESS" required>
            <button type="submit" class="btn btn-black">SUBSCRIBE</button>
        </form>
    </div>
</section>
