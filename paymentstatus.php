<?php
include('./dbConnection.php');
include('./includes/header.php');

header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

// following files need to be included
require_once("./PaytmKit/lib/config_paytm.php");
require_once("./PaytmKit/lib/encdec_paytm.php");

$ORDER_ID = "";
$requestParamList = array();
$responseParamList = array();

if (isset($_POST["ORDER_ID"]) && $_POST["ORDER_ID"] != "") {
    $ORDER_ID = $_POST["ORDER_ID"];
    $requestParamList = array("MID" => PAYTM_MERCHANT_MID , "ORDERID" => $ORDER_ID);  
    $StatusCheckSum = getChecksumFromArray($requestParamList, PAYTM_MERCHANT_KEY);
    $requestParamList['CHECKSUMHASH'] = $StatusCheckSum;
    $responseParamList = getTxnStatusNew($requestParamList);
    
    // Update order status if payment successful
    if(isset($responseParamList['STATUS']) && $responseParamList['STATUS'] == 'TXN_SUCCESS') {
        $update_sql = "UPDATE orders SET payment_status = 'completed', txn_id = ? WHERE order_no = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $responseParamList['TXNID'], $ORDER_ID);
        $update_stmt->execute();
    }
}
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <i class="fas fa-receipt fa-3x text-primary mb-3"></i>
                    <h2 class="fw-bold">حالة الدفع</h2>
                    <p class="text-muted">تحقق من حالة معاملتك</p>
                </div>
                
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-body p-4">
                        <form method="post" action="">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                                <input type="text" class="form-control" name="ORDER_ID" 
                                       placeholder="أدخل رقم الطلب" value="<?php echo $ORDER_ID; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> تحقق
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (isset($responseParamList) && count($responseParamList) > 0) { ?>
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-primary text-white p-4 rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> إيصال الدفع</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if($responseParamList['STATUS'] == 'TXN_SUCCESS') { ?>
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle me-2"></i> تمت عملية الدفع بنجاح!
                        </div>
                        <?php } else { ?>
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i> حالة الدفع: <?php echo $responseParamList['STATUS']; ?>
                        </div>
                        <?php } ?>
                        
                        <table class="table table-bordered">
                            <tbody>
                                <?php foreach($responseParamList as $paramName => $paramValue) { 
                                    if(($paramName == "TXNID") || ($paramName == "ORDERID") || 
                                       ($paramName == "TXNAMOUNT") || ($paramName == "STATUS") ||
                                       ($paramName == "TXNDATE")) { ?>
                                <tr>
                                    <td class="fw-bold bg-light" style="width: 40%;">
                                        <?php 
                                        $labels = [
                                            'TXNID' => 'رقم المعاملة',
                                            'ORDERID' => 'رقم الطلب',
                                            'TXNAMOUNT' => 'المبلغ',
                                            'STATUS' => 'الحالة',
                                            'TXNDATE' => 'التاريخ'
                                        ];
                                        echo $labels[$paramName] ?? $paramName;
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if($paramName == 'STATUS') {
                                            if($paramValue == 'TXN_SUCCESS') {
                                                echo '<span class="badge bg-success">ناجحة</span>';
                                            } else {
                                                echo '<span class="badge bg-warning">' . $paramValue . '</span>';
                                            }
                                        } else {
                                            echo $paramValue;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php } } ?>
                            </tbody>
                        </table>
                        
                        <div class="text-center mt-4">
                            <button class="btn btn-primary rounded-pill px-5" onclick="window.print();">
                                <i class="fas fa-print me-2"></i> طباعة الإيصال
                            </button>
                            <a href="student/mycourses.php" class="btn btn-outline-primary rounded-pill px-5 ms-2">
                                <i class="fas fa-book me-2"></i> دوراتي
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<?php include('./includes/footer.php'); ?>