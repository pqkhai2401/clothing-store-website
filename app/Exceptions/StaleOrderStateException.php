<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Ném khi trạng thái đơn hàng đã bị một luồng khác (admin khác, 2 tab, webhook/cron)
 * thay đổi giữa lúc KIỂM TRA (đọc ngoài transaction) và lúc GHI (trong transaction có
 * khóa lockForUpdate). Controller bắt exception này để trả về "đơn vừa thay đổi, tải
 * lại trang" thay vì ghi đè lên thay đổi kia (tránh oversell / trạng thái lệch với kho).
 */
class StaleOrderStateException extends RuntimeException
{
}
