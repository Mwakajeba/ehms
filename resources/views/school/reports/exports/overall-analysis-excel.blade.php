<table>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold; font-size: 14px;">
            {{ $company->name ?? 'School Management System' }}
        </td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold; font-size: 12px;">
            OVERALL ANALYSIS REPORT
        </td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center; font-size: 10px;">
            Generated on: {{ $generatedAt }}
        </td>
    </tr>
    @if(isset($filters['academic_year']) || isset($filters['exam_type']))
    <tr>
        <td colspan="12" style="font-size: 10px;">
            @if(isset($filters['academic_year']))<strong>Academic Year:</strong> {{ $filters['academic_year'] }}@endif
            @if(isset($filters['exam_type'])) | <strong>Exam Type:</strong> {{ $filters['exam_type'] }}@endif
        </td>
    </tr>
    @endif
    <tr><td colspan="12"></td></tr>
    <tr>
        <th rowspan="2">S/N</th>
        <th rowspan="2">Class</th>
        <th rowspan="2">Stream</th>
        <th rowspan="2">No. of Students</th>
        <th colspan="5">Grade Distribution</th>
        <th rowspan="2">Class Mean</th>
        <th rowspan="2">Grade</th>
        <th rowspan="2">Class Teacher</th>
    </tr>
    <tr>
        <th>A</th>
        <th>B</th>
        <th>C</th>
        <th>D</th>
        <th>E</th>
    </tr>
    @php $serial = 1; @endphp
    @foreach($analysis as $item)
    <tr>
        <td>{{ $serial++ }}</td>
        <td>{{ $item['class']->name }}</td>
        <td>{{ $item['stream']->name }}</td>
        <td>{{ $item['students'] }}</td>
        <td>{{ $item['grade_counts']['A'] }}</td>
        <td>{{ $item['grade_counts']['B'] }}</td>
        <td>{{ $item['grade_counts']['C'] }}</td>
        <td>{{ $item['grade_counts']['D'] }}</td>
        <td>{{ $item['grade_counts']['E'] }}</td>
        <td>{{ number_format($item['class_mean'], 2) }}</td>
        <td>{{ $item['grade'] }}</td>
        <td>{{ $item['class_teacher'] }}</td>
    </tr>
    @endforeach

    <!-- Subtotals -->
    @foreach($subtotals as $categoryName => $subtotal)
    <tr>
        <td colspan="3" style="text-align: right; font-weight: bold;">SUBTOTAL - {{ $categoryName }}</td>
        <td style="font-weight: bold;">{{ $subtotal['students'] }}</td>
        <td style="font-weight: bold;">{{ $subtotal['grade_counts']['A'] }}</td>
        <td style="font-weight: bold;">{{ $subtotal['grade_counts']['B'] }}</td>
        <td style="font-weight: bold;">{{ $subtotal['grade_counts']['C'] }}</td>
        <td style="font-weight: bold;">{{ $subtotal['grade_counts']['D'] }}</td>
        <td style="font-weight: bold;">{{ $subtotal['grade_counts']['E'] }}</td>
        <td style="font-weight: bold;">{{ number_format($subtotal['total_mean'], 2) }}</td>
        <td style="font-weight: bold;">{{ $subtotal['grade'] }}</td>
        <td></td>
    </tr>
    @endforeach

    <!-- Grand Total -->
    <tr>
        <td colspan="3" style="text-align: right; font-weight: bold;">GRAND TOTAL</td>
        <td style="font-weight: bold;">{{ $grandTotal['students'] }}</td>
        <td style="font-weight: bold;">{{ $grandTotal['grade_counts']['A'] }}</td>
        <td style="font-weight: bold;">{{ $grandTotal['grade_counts']['B'] }}</td>
        <td style="font-weight: bold;">{{ $grandTotal['grade_counts']['C'] }}</td>
        <td style="font-weight: bold;">{{ $grandTotal['grade_counts']['D'] }}</td>
        <td style="font-weight: bold;">{{ $grandTotal['grade_counts']['E'] }}</td>
        <td style="font-weight: bold;">{{ number_format($grandTotal['total_mean'], 2) }}</td>
        <td style="font-weight: bold;">{{ $grandTotal['grade'] }}</td>
        <td></td>
    </tr>

    <!-- Summary Section -->
    <tr><td colspan="12"></td></tr>
    <tr>
        <td colspan="6" style="font-weight: bold; border-top: 1px solid #000;">PERFORMANCE SUMMARY</td>
        <td colspan="6" style="font-weight: bold; border-top: 1px solid #000;">GRADE DISTRIBUTION SUMMARY</td>
    </tr>
    <tr>
        <td colspan="2">Total Students:</td>
        <td colspan="4">{{ $grandTotal['students'] }}</td>
        <td>Grade A:</td>
        <td>{{ $grandTotal['grade_counts']['A'] }}</td>
        <td colspan="3">{{ $grandTotal['students'] > 0 ? number_format(($grandTotal['grade_counts']['A'] / $grandTotal['students']) * 100, 1) : 0 }}%</td>
    </tr>
    <tr>
        <td colspan="2">Total Classes/Streams:</td>
        <td colspan="4">{{ count($analysis) }}</td>
        <td>Grade B:</td>
        <td>{{ $grandTotal['grade_counts']['B'] }}</td>
        <td colspan="3">{{ $grandTotal['students'] > 0 ? number_format(($grandTotal['grade_counts']['B'] / $grandTotal['students']) * 100, 1) : 0 }}%</td>
    </tr>
    <tr>
        <td colspan="2">Overall Mean:</td>
        <td colspan="4">{{ number_format($grandTotal['total_mean'], 2) }}</td>
        <td>Grade C:</td>
        <td>{{ $grandTotal['grade_counts']['C'] }}</td>
        <td colspan="3">{{ $grandTotal['students'] > 0 ? number_format(($grandTotal['grade_counts']['C'] / $grandTotal['students']) * 100, 1) : 0 }}%</td>
    </tr>
    <tr>
        <td colspan="2">Overall Grade:</td>
        <td colspan="4">{{ $grandTotal['grade'] }}</td>
        <td>Grade D:</td>
        <td>{{ $grandTotal['grade_counts']['D'] }}</td>
        <td colspan="3">{{ $grandTotal['students'] > 0 ? number_format(($grandTotal['grade_counts']['D'] / $grandTotal['students']) * 100, 1) : 0 }}%</td>
    </tr>
    <tr>
        <td colspan="6"></td>
        <td>Grade E:</td>
        <td>{{ $grandTotal['grade_counts']['E'] }}</td>
        <td colspan="3">{{ $grandTotal['students'] > 0 ? number_format(($grandTotal['grade_counts']['E'] / $grandTotal['students']) * 100, 1) : 0 }}%</td>
    </tr>
</table>