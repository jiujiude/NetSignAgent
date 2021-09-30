/**
 * 前端组件
 * By hgq <393210556@qq.com> 2021/09/29 16:22
 */
class NetSignAgent {

    constructor(data) {
        this.obj = new IWSAgent();
        this.type = data.type; //1后端回调中验签 2前端验签
        this.callback = data.callback;
        this.defultDN = data.defultDN;
    }

    /**
     * 开始签名
     */
    start_sign() {
        var CertStoreSM2 = ""; //国密证书存储区，没有填“”
        var CertStoreRSA = "Sign"; //RSA证书存储区，全部、加密、签名
        var defultDN = this.defultDN;
        var Keyspec = 2; //按密钥用法过滤，为0只返回全部证书,为2只返回签名证书

        this.obj.IWSAGetAllCertsListInfoByCertDN(CertStoreSM2, CertStoreRSA, defultDN, Keyspec, this.cert_succeed.bind(this));
    }

    /**
     * 证书回调
     * @param CertListData
     */
    cert_succeed(CertListData) {
        if (CertListData.length == 0) {
            alert("识别不到Ukey");
            return;
        }
        var cert = CertListData[0];
        //进行签名
        this.make_sign(cert);
    }

    /**
     * 签名
     * @param cert
     */
    make_sign(cert) {
        //签名
        var PlainText = 'sign'; //原文
        var CertIndex = cert; //证书
        var DigestArithmetic = 'SHA1'; //摘要算法

        this.obj.IWSAAttachedSign(2, PlainText, CertIndex, DigestArithmetic, this.sign_succeed.bind(this));
    }

    /**
     * 签名回调
     * @param errorCode
     * @param signedData
     */
    sign_succeed(errorCode, signedData) {//普通Key Attached签名  成功后数据处理
        if (errorCode != 0) {
            alert(errorCode + '签名失败');
        } else {
            if (this.type == 1) {
                this.callback.call('', signedData)
            } else {
                document.getElementById('signedData').value = signedData;
                //前端验签
                this.obj.IWSAAttachedVerify(2, signedData, this.verify_succeed.bind(this));
            }
        }
    }

    /**
     * 前端验签回调
     * @param errorCode
     * @param PlainText
     * @param certDN
     */
    verify_succeed(errorCode, PlainText, certDN) {
        //普通Key Attached验证测试 成功后数据处理
        if (errorCode == 0) {
            // console.log(PlainText);
            // console.log(certDN);
            this.callback.call('', certDN)
        } else {
            alert(errorCode);
        }
    }
}