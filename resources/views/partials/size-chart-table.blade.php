{{--
    Partial: partials.size-chart-table

    Vẽ bảng số đo cho MỘT giới tính (Nam hoặc Nữ).

    Biến đầu vào:
    - $type: 'men' | 'women' -> quyết định tên cột hiển thị.
    - $rows: mảng dữ liệu từng size, ví dụ:
        women: ['size' => 'S', 'bust' => 82, 'waist' => 66, 'hip' => 86]
        men:   ['size' => 'S', 'chest' => 88, 'waist' => 72, 'height' => '160 - 165']
--}}

<div class="table-responsive">
    <table class="table size-chart-table mb-0">
        <thead>
            <tr>
                <th>Size</th>
                @if($type === 'women')
                    <th>Vòng ngực (cm)</th>
                    <th>Vòng eo (cm)</th>
                    <th>Vòng mông (cm)</th>
                @else
                    <th>Vòng ngực (cm)</th>
                    <th>Vòng eo (cm)</th>
                    <th>Chiều cao (cm)</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td class="fw-bold">{{ $row['size'] }}</td>
                    @if($type === 'women')
                        <td>{{ $row['bust'] }}</td>
                        <td>{{ $row['waist'] }}</td>
                        <td>{{ $row['hip'] }}</td>
                    @else
                        <td>{{ $row['chest'] }}</td>
                        <td>{{ $row['waist'] }}</td>
                        <td>{{ $row['height'] }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
