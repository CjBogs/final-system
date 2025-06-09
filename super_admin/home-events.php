<?php
require_once '../config.php';
require_once '../helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$name = 'Admin';

if (!empty($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];

    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name);

        if ($stmt->fetch()) {
            $name = trim($first_name . ' ' . $last_name);
        }

        $stmt->close();
    }
}

$query = "SELECT * FROM events WHERE status = 'pending' ORDER BY event_date DESC";
$allEvents = mysqli_query($conn, $query);

if (!$allEvents) {
    die("Error fetching events: " . mysqli_error($conn));
}

function statusIcon($status)
{
    return match ($status) {
        'approved' => '<svg class="inline w-4 h-4 mr-1 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        'rejected' => '<svg class="inline w-4 h-4 mr-1 text-red-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        default => '<svg class="inline w-4 h-4 mr-1 text-yellow-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>',
    };
}
?>

<div class="max-w-4xl mx-auto px-4" x-data="{ showModal: false, actionType: '', eventId: null }" x-cloak>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-semibold text-[#1D503A]">Welcome, <?= htmlspecialchars($name) ?>!</h2>
        <p class="text-lg text-[#4A5D4C] border-b-2 pb-2 border-[#1D503A]">Manage your events.</p>
    </div>

    <?php if (mysqli_num_rows($allEvents) > 0): ?>
        <div class="overflow-x-auto bg-white rounded-xl shadow border border-[#1D503A]">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="bg-[#E5E1DB] text-xs text-[#1D503A] uppercase">
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Created By</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
            </table>
            <!-- Scrollable table body -->
            <div class="max-h-[360px] overflow-y-auto">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($allEvents)): ?>
                            <?php
                            $status = $row['status'];
                            $statusColor = match ($status) {
                                'approved' => 'green',
                                'rejected' => 'red',
                                default => 'yellow',
                            };
                            ?>
                            <tr class="border-b hover:bg-[#FAF5EE]">
                                <td class="px-4 py-3 font-medium text-[#1D503A]"><?= htmlspecialchars($row['title']) ?></td>
                                <td class="px-4 py-3 text-[#1D503A]"><?= date('F j, Y', strtotime($row['event_date'])) ?></td>
                                <td class="px-4 py-3 max-w-xs truncate text-[#1D503A]" title="<?= htmlspecialchars($row['description']) ?>"><?= truncate($row['description']) ?></td>
                                <td class="px-4 py-3 text-[#1D503A]"><?= htmlspecialchars($row['user_email']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold 
                                    <?= $statusColor === 'green' ? 'bg-green-100 text-green-700' : ($statusColor === 'red' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                                        <?= statusIcon($status) ?>
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center space-y-1">
                                    <?php if ($status === 'pending'): ?>
                                        <div class="inline-flex space-x-2">
                                            <button
                                                type="button"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm font-semibold"
                                                @click="showModal = true; actionType = 'approve'; eventId = <?= $row['id'] ?>;">
                                                Approve
                                            </button>
                                            <button
                                                type="button"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-semibold"
                                                @click="showModal = true; actionType = 'reject'; eventId = <?= $row['id'] ?>;">
                                                Reject
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        &mdash;
                                    <?php endif; ?>

                                    <button type="button"
                                        class="view-details-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-semibold"
                                        data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>"
                                        data-date="<?= htmlspecialchars(date('F j, Y', strtotime($row['event_date'])), ENT_QUOTES) ?>"
                                        data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                                        data-user="<?= htmlspecialchars($row['user_email'], ENT_QUOTES) ?>"
                                        data-status="<?= htmlspecialchars($status, ENT_QUOTES) ?>">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center text-[#1D503A] select-none" role="alert" aria-live="polite"
            style="margin: 0.5rem auto; border: 2px dashed #1D503A; border-radius: 1rem; background-color: #FAF5EE; max-width: 900px; width: 100%; padding: 2rem 1rem;">
            <svg class="mx-auto mb-4 w-20 h-20 text-[#1D503A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2a2 2 0 0 1 2 2v3H6V4a2 2 0 0 1 2-2h8zm4 7v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h16zm-5 3H9v1h6v-1z" />
            </svg>
            <p class="text-lg font-semibold">No events requested.</p>
            <p class="text-sm text-[#4F766E]">Please check back later for updates.</p>
        </div>
    <?php endif; ?>

    <!-- Approve/Reject Reason Modal -->
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="showModal = false">
        <div
            @click.away="showModal = false"
            class="bg-white rounded-lg max-w-md w-full p-6 shadow-lg relative"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90">
            <button
                @click="showModal = false"
                class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl font-bold"
                aria-label="Close modal">&times;</button>

            <h2 class="text-xl font-semibold mb-4 text-[#1D503A]" x-text="actionType === 'approve' ? 'Approval Reason' : 'Rejection Reason'"></h2>

            <form action="update-event-status.php" method="POST" class="space-y-4">
                <input type="hidden" name="event_id" :value="eventId">
                <input type="hidden" name="action" :value="actionType">

                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                    <textarea
                        id="reason"
                        name="reason"
                        rows="4"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:ring-[#1D503A] focus:border-[#1D503A]"></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-[#1D503A] hover:bg-[#163b2c] text-white rounded-md font-semibold">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event View Details Modal (Your existing modal, slightly styled to fit) -->
<div id="event-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
    <div class="bg-white rounded-lg max-w-lg w-full mx-4 p-6 relative shadow-lg">
        <button id="modal-close-btn" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl font-bold" aria-label="Close modal">&times;</button>
        <h3 id="modal-title" class="text-xl font-semibold mb-2 text-[#1D503A]"></h3>
        <p id="modal-date" class="text-sm text-gray-600 mb-4"></p>
        <p id="modal-description" class="text-gray-700 mb-4"></p>
        <p id="modal-user" class="text-sm text-gray-600"></p>
        <p id="modal-status" class="text-sm mt-2 font-semibold"></p>
    </div>
</div>

<script>
    // Simple truncate helper function (client side)
    function truncate(text, length = 30) {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    }

    // View Details modal logic
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById('event-modal');
            document.getElementById('modal-title').textContent = btn.dataset.title;
            document.getElementById('modal-date').textContent = btn.dataset.date;
            document.getElementById('modal-description').textContent = btn.dataset.description;
            document.getElementById('modal-user').textContent = 'Requested by: ' + btn.dataset.user;
            document.getElementById('modal-status').textContent = 'Status: ' + btn.dataset.status.charAt(0).toUpperCase() + btn.dataset.status.slice(1);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    document.getElementById('modal-close-btn').addEventListener('click', () => {
        const modal = document.getElementById('event-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
</script>