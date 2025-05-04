<template>
    <div class="payment-container">
        <a-card title="医疗费用缴纳" :bordered="false">
            <!-- 处方缴费列表 -->
            <div class="section">
                <h3 class="section-title">处方缴费列表</h3>
                <a-table
                    :columns="prescriptionColumns"
                    :data-source="prescriptions"
                    :row-key="record => record.pres_id"
                    :row-selection="rowSelection"
                    :pagination="prescriptionPagination"
                    :loading="loading"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'status'">
                            <a-tag :color="getStatusColor(record.status_code, 'prescription')">
                                {{ getStatusText(record.status_code, 'prescription') }}
                            </a-tag>
                        </template>
                        <template v-else-if="column.key === 'amount'">
                            ¥{{ parseFloat(record.sum_price).toFixed(2) }}
                        </template>
                        <template v-else-if="column.key === 'date'">
                            <!-- 假设数据中有日期字段，这里暂用示例 -->
                            {{ record.date || '-' }}
                        </template>
                        <template v-else-if="column.key === 'action'">
                            <a-button type="link" size="small" @click="viewPrescriptionDetail(record)">
                                查看详情
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </div>

            <!-- 已选处方汇总 -->
            <div class="section" v-if="selectedPrescriptions.length > 0">
                <h3 class="section-title">缴费汇总</h3>
                <a-descriptions bordered :column="4" size="small">
                    <a-descriptions-item label="处方数量">{{ selectedPrescriptions.length }}张</a-descriptions-item>
                    <a-descriptions-item label="药品数量">{{ totalDrugsCount }}种</a-descriptions-item>
                    <a-descriptions-item label="总金额">
                        <span class="total-amount">¥{{ totalAmount.toFixed(2) }}</span>
                    </a-descriptions-item>
                    <a-descriptions-item label="优惠金额">-¥{{ discountAmount.toFixed(2) }}</a-descriptions-item>
                    <a-descriptions-item label="实付金额" :span="4">
                        <span class="actual-amount">¥{{ actualAmount.toFixed(2) }}</span>
                    </a-descriptions-item>
                </a-descriptions>
            </div>

            <!-- 支付方式选择 -->
            <div class="section" v-if="selectedPrescriptions.length > 0">
                <h3 class="section-title">支付方式</h3>
                <a-radio-group v-model:value="paymentMethod" style="width: 100%">
                    <a-row :gutter="[16, 16]">
                        <a-col :span="8">
                            <a-radio-button value="wechat">
                                <div class="payment-method">
                                    <WechatOutlined style="color: #09BB07; font-size: 24px;" />
                                    <span style="margin-left: 8px;">微信支付</span>
                                </div>
                            </a-radio-button>
                        </a-col>
                        <a-col :span="8">
                            <a-radio-button value="alipay">
                                <div class="payment-method">
                                    <AlipayOutlined style="color: #1677FF; font-size: 24px;" />
                                    <span style="margin-left: 8px;">支付宝</span>
                                </div>
                            </a-radio-button>
                        </a-col>
                        <a-col :span="8">
                            <a-radio-button value="bank">
                                <div class="payment-method">
                                    <BankOutlined style="color: #722ED1; font-size: 24px;" />
                                    <span style="margin-left: 8px;">银行卡支付</span>
                                </div>
                            </a-radio-button>
                        </a-col>
                    </a-row>
                </a-radio-group>

                <!-- 优惠券选择 -->
                <div class="coupon-section" v-if="availableCoupons.length > 0">
                    <h4>优惠券</h4>
                    <a-select
                        v-model:value="selectedCoupon"
                        placeholder="请选择优惠券"
                        style="width: 100%"
                        allow-clear
                    >
                        <a-select-option 
                            v-for="coupon in availableCoupons" 
                            :key="coupon.id"
                            :value="coupon.id"
                        >
                            {{ coupon.name }} ({{ coupon.type === 'fixed' ? `¥${coupon.value}` : `${coupon.value}折` }})
                            <span v-if="coupon.condition">，满{{ coupon.condition }}元可用</span>
                        </a-select-option>
                    </a-select>
                </div>

                <!-- 支付按钮 -->
                <div class="payment-actions">
                    <a-button 
                        type="primary" 
                        size="large" 
                        :loading="paying"
                        @click="handlePayment"
                    >
                        立即支付 ¥{{ actualAmount.toFixed(2) }}
                    </a-button>
                </div>
            </div>

            <!-- 患者缴费检查项目表格 -->
            <div class="section">
                <h3 class="section-title">患者缴费检查项目</h3>
                <a-table
                    :columns="checkupColumns"
                    :data-source="checkupRecords"
                    :row-key="record => record.exam_id"
                    :pagination="checkupPagination"
                    :loading="loadingCheckups"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'status'">
                            <a-tag :color="getStatusColor(record.status_code, 'checkup')">
                                {{ getStatusText(record.status_code, 'checkup') }}
                            </a-tag>
                        </template>
                        <template v-else-if="column.key === 'oper_time'">
                            {{ formatDateTime(record.oper_time) }}
                        </template>
                        <template v-else-if="column.key === 'exam_name'">
                            {{ getExamName(record.exam_def_id) }}
                        </template>
                        <template v-else-if="column.key === 'amount'">
                            ¥{{ getExamPrice(record.exam_def_id) }}
                        </template>
                        <template v-else-if="column.key === 'action'">
                            <a-button
                                size="small"
                                :disabled="record.status_code!== 0"
                                @click="payForCheckup(record.exam_id)"
                            >
                                付款
                            </a-button>
                            <a-button
                                size="small"
                                :disabled="record.status_code!== 2"
                                @click="showCheckupResultModal(record)"
                            >
                                查看结果
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </div>

            <!-- 历史缴费记录 -->
            <div class="section">
                <h3 class="section-title">历史缴费记录</h3>
                <a-table
                    :columns="paymentHistoryColumns"
                    :data-source="paymentHistory"
                    :row-key="record => record.id"
                    :pagination="historyPagination"
                    :loading="loadingHistory"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'status'">
                            <a-tag :color="record.status === 'success' ? 'green' : 'red'">
                                {{ record.status === 'success' ? '支付成功' : '支付失败' }}
                            </a-tag>
                        </template>
                        <template v-else-if="column.key === 'amount'">
                            ¥{{ record.amount.toFixed(2) }}
                        </template>
                        <template v-else-if="column.key === 'date'">
                            {{ formatDateTime(record.date) }}
                        </template>
                        <template v-else-if="column.key === 'method'">
                            {{ getPaymentMethodText(record.method) }}
                        </template>
                        <template v-else-if="column.key === 'action'">
                            <a-button type="link" size="small" @click="viewPaymentDetail(record)">
                                查看详情
                            </a-button>
                            <a-button 
                                type="link" 
                                size="small" 
                                v-if="record.status === 'success'"
                                @click="downloadInvoice(record)"
                            >
                                下载发票
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </div>
        </a-card>

        <!-- 处方详情模态框 -->
        <a-modal
            v-model:visible="prescriptionDetailVisible"
            :title="currentPrescription ? `处方详情 - ${currentPrescription.pres_id}` : ''"
            width="800px"
            :footer="null"
        >
            <PrescriptionDetail v-if="currentPrescription" :prescription="currentPrescription" />
        </a-modal>

        <!-- 支付详情模态框 -->
        <a-modal
            v-model:visible="paymentDetailVisible"
            :title="currentPayment ? `支付详情 - ${currentPayment.id}` : ''"
            width="700px"
            :footer="null"
        >
            <PaymentDetail v-if="currentPayment" :payment="currentPayment" />
        </a-modal>

        <!-- 支付结果模态框 -->
        <a-modal
            v-model:visible="paymentResultVisible"
            :title="paymentResult.success ? '支付成功' : '支付失败'"
            width="500px"
            :closable="false"
            :maskClosable="false"
            :footer="null"
        >
            <div class="payment-result">
                <div v-if="paymentResult.success" class="success-result">
                    <CheckCircleOutlined style="color: #52c41a; font-size: 48px;" />
                    <h3>支付成功！</h3>
                    <p>支付金额：¥{{ paymentResult.amount.toFixed(2) }}</p>
                    <p>支付方式：{{ getPaymentMethodText(paymentResult.method) }}</p>
                    <p>交易单号：{{ paymentResult.transactionId }}</p>
                    <p>支付时间：{{ formatDateTime(paymentResult.time) }}</p>
                    <div class="result-actions">
                        <a-button type="primary" @click="handlePaymentSuccess">查看处方</a-button>
                        <a-button @click="downloadInvoice(paymentResult)">下载发票</a-button>
                    </div>
                </div>
                <div v-else class="fail-result">
                    <CloseCircleOutlined style="color: #f5222d; font-size: 48px;" />
                    <h3>支付失败！</h3>
                    <p>失败原因：{{ paymentResult.message || '未知错误' }}</p>
                    <div class="result-actions">
                        <a-button type="primary" @click="paymentResultVisible = false">返回</a-button>
                        <a-button @click="handlePayment">重新支付</a-button>
                    </div>
                </div>
            </div>
        </a-modal>

        <!-- 检查结果模态框 -->
        <a-modal
            v-model:visible="checkupResultModalVisible"
            :title="`检查记录 (ID: ${currentCheckupRecord?.exam_id}) 结果`"
            width="800px"
            :mask-closable="false"
            :destroy-on-close="true"
            @cancel="hideCheckupResultModal"
            @ok="hideCheckupResultModal"
        >
            <div v-if="currentCheckupResult">
                <p>检查结果：{{ currentCheckupResult.exam_result }}</p>
            </div>
        </a-modal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { message } from 'ant-design-vue'
import dayjs from 'dayjs'
import { 
  WechatOutlined, 
  AlipayOutlined, 
  BankOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined
} from '@ant-design/icons-vue'
import PrescriptionDetail from '../../components/patient/PrescriptionDetail.vue'
import PaymentDetail from '../../components/patient/PaymentDetail.vue'
import { getCheckupRecord, getAppoint, getLoginInfo, getCheckupResult, payCheckup, getPrescriptions, payPres, getCheckup } from '@/api/api'

// 处方表格列定义
const prescriptionColumns = [
  {
      title: '处方编号',
      dataIndex: 'pres_id',
      key: 'pres_id',
      width: 120
  },
  {
      title: '开具日期',
      dataIndex: 'oper_time',
      key: 'oper_time',
      width: 120
  },
  {
      title: '开具医生',
      dataIndex: 'doc_name',
      key: 'doc_name',
      width: 150,
      customRender: ({ text }) => `${text}`
  },
  {
      title: '药品名称',
      dataIndex: 'drug_name',
      key: 'drug_name',
      width: 150
  },
  {
      title: '金额(元)',
      dataIndex: 'sum_price',
      key: 'amount',
      width: 120
  },
  {
      title: '状态',
      dataIndex: 'status_code',
      key: 'status',
      width: 100
  },
  {
      title: '操作',
      key: 'action',
      width: 100
  }
]

// 缴费历史表格列定义
const paymentHistoryColumns = [
  {
      title: '缴费单号',
      dataIndex: 'id',
      key: 'id',
      width: 120
  },
  {
      title: '缴费时间',
      dataIndex: 'date',
      key: 'date',
      width: 150
  },
  {
      title: '支付方式',
      dataIndex: 'method',
      key: 'method',
      width: 120
  },
  {
      title: '金额(元)',
      dataIndex: 'amount',
      key: 'amount',
      width: 120
  },
  {
      title: '状态',
      dataIndex: 'status',
      key: 'status',
      width: 100
  },
  {
      title: '操作',
      key: 'action',
      width: 150
  }
]

// 检查项目表格列定义
const checkupColumns = [
  {
      title: '检查记录ID',
      dataIndex: 'exam_id',
      key: 'exam_id',
      width: 100
  },
  {
      title: '检查项目名称',
      dataIndex: 'exam_name',
      key: 'exam_name',
      width: 150
  },
  {
      title: '金额(元)',
      dataIndex: 'amount',
      key: 'amount',
      width: 120
  },
  {
      title: '状态',
      dataIndex: 'status_code',
      key: 'status',
      width: 120
  },
  {
      title: '状态更新时间',
      dataIndex: 'oper_time',
      key: 'oper_time',
      width: 200
  },
  {
      title: '操作',
      key: 'action',
      width: 250
  }
]

// 分页配置
const prescriptionPagination = ref({
  current: 1,
  pageSize: 5,
  showSizeChanger: true,
  pageSizeOptions: ['5', '10', '20']
})

const historyPagination = ref({
  current: 1,
  pageSize: 5,
  showSizeChanger: true,
  pageSizeOptions: ['5', '10', '20']
})

const checkupPagination = ref({
  current: 1,
  pageSize: 5,
  showSizeChanger: true,
  pageSizeOptions: ['5', '10', '20']
})

// 数据状态
const prescriptions = ref([])
const selectedPrescriptionIds = ref([])
const paymentHistory = ref([])
const loading = ref(false)
const loadingHistory = ref(false)
const paying = ref(false)
const paymentMethod = ref('wechat')
const availableCoupons = ref([])
const selectedCoupon = ref(null)
const checkupRecords = ref([])
const loadingCheckups = ref(false)
const currentCheckupRecord = ref(null)
const currentCheckupResult = ref(null)
const checkupResultModalVisible = ref(false)
const allExams = ref([])
const examMap = ref({})

// 模态框状态
const prescriptionDetailVisible = ref(false)
const paymentDetailVisible = ref(false)
const paymentResultVisible = ref(false)
const currentPrescription = ref(null)
const currentPayment = ref(null)
const paymentResult = ref({
  success: false,
  amount: 0,
  method: '',
  transactionId: '',
  time: '',
  message: ''
})

// 计算属性
const selectedPrescriptions = computed(() => {
  return prescriptions.value.filter(p => selectedPrescriptionIds.value.includes(p.pres_id))
})

const totalAmount = computed(() => {
  return selectedPrescriptions.value.reduce((sum, p) => sum + parseFloat(p.sum_price), 0)
})

const totalDrugsCount = computed(() => {
  return selectedPrescriptions.value.reduce((sum, p) => sum + parseInt(p.total_amount), 0)
})

const discountAmount = computed(() => {
  if (!selectedCoupon.value) return 0
  
  const coupon = availableCoupons.value.find(c => c.id === selectedCoupon.value)
  if (!coupon) return 0
  
  // 检查是否满足使用条件
  if (coupon.condition && totalAmount.value < coupon.condition) {
      return 0
  }
  
  // 计算优惠金额
  if (coupon.type === 'fixed') {
      return coupon.value
  } else if (coupon.type === 'discount') {
      return totalAmount.value * (1 - coupon.value / 10)
  }
  
  return 0
})

const actualAmount = computed(() => {
  return totalAmount.value - discountAmount.value
})

const rowSelection = computed(() => {
  return {
      selectedRowKeys: selectedPrescriptionIds.value,
      onChange: (selectedRowKeys) => {
          selectedPrescriptionIds.value = selectedRowKeys
      },
      getCheckboxProps: (record) => ({
          disabled: record.status_code!== 0
      })
  }
})

// 方法
const formatDate = date => {
  return date ? dayjs(date).format('YYYY-MM-DD') : '-'
}

const formatDateTime = datetime => {
  return datetime ? dayjs(datetime).format('YYYY-MM-DD HH:mm:ss') : '-'
}

const getPaymentMethodText = method => {
  const map = {
      wechat: '微信支付',
      alipay: '支付宝',
      bank: '银行卡'
  }
  return map[method] || method
}

const getStatusText = (statusCode, type) => {
  if (type === 'prescription') {
    const statusMap = {
        0: '开药',
        1: '已付款',
        2: '已付款已发药'
    }
    return statusMap[statusCode] || '未知状态'
  } else if (type === 'checkup') {
    const statusMap = {
        0: '待付款',
        1: '已付款',
        2: '已付款已检查'
    }
    return statusMap[statusCode] || '未知状态'
  }
}

const getStatusColor = (statusCode, type) => {
  if (type === 'prescription') {
    const colorMap = {
        0: 'red',
        1: 'green',
        2: 'green'
    }
    return colorMap[statusCode] || 'default'
  } else if (type === 'checkup') {
    const colorMap = {
        0: 'red',
        1: 'green',
        2: 'green'
    }
    return colorMap[statusCode] || 'default'
  }
}

const getExamName = (examId) => {
  return examMap.value[examId]?.exam_name || '未知检查项目';
}

const getExamPrice = (examId) => {
  return examMap.value[examId]?.exam_price || '0.00';
}

const fetchPrescriptions = async () => {
  try {
      loading.value = true
      const response = await getPrescriptions()
      if (response.data.code === 200) {
          prescriptions.value = response.data.data
      } else {
          message.error('获取处方数据失败')
      }
  } catch (error) {
      message.error('获取处方数据失败')
  } finally {
      loading.value = false
  }
}

const fetchPaymentHistory = async () => {
  try {
      loadingHistory.value = true
      // 模拟API调用
      await new Promise(resolve => setTimeout(resolve, 800))
      
      // 模拟数据
      paymentHistory.value = generateMockPaymentHistory()
  } catch (error) {
      message.error('获取缴费历史失败')
  } finally {
      loadingHistory.value = false
  }
}

const fetchAvailableCoupons = async () => {
  try {
      // 模拟API调用
      await new Promise(resolve => setTimeout(resolve, 500))
      
      // 模拟数据
      availableCoupons.value = [
          { id: 'c1', name: '新用户立减', type: 'fixed', value: 10, condition: 50 },
          { id: 'c2', name: '药品折扣券', type: 'discount', value: 8.8, condition: 100 },
          { id: 'c3', name: '满减券', type: 'fixed', value: 20, condition: 150 }
      ]
  } catch (error) {
      console.error('获取优惠券失败', error)
  }
}

const generateMockPrescriptions = () => {
  const doctors = [
      { name: '张医生', title: '主任医师' },
      { name: '李医生', title: '副主任医师' },
      { name: '王医生', title: '主治医师' }
  ]
  
  const prescriptions = []
  for (let i = 1; i <= 15; i++) {
      const doctor = doctors[Math.floor(Math.random() * doctors.length)]
      const daysOffset = Math.floor(Math.random() * 30) - 15 // -15到15天
      const status = i % 3 === 0 ? 'paid' : 'unpaid'
      
      prescriptions.push({
          id: `RX${1000 + i}`,
          date: dayjs().add(daysOffset, 'day').format('YYYY-MM-DD'),
          doctor: doctor,
          drugsCount: Math.floor(Math.random() * 5) + 1,
          amount: parseFloat((Math.random() * 200 + 50).toFixed(2)),
          status: status,
          drugs: generateMockDrugs(Math.floor(Math.random() * 5) + 1),
          diagnosis: ['上呼吸道感染', '急性肠胃炎', '高血压', '糖尿病', '普通感冒'][Math.floor(Math.random() * 5)],
          notes: i % 2 === 0 ? '饭后服用，一日三次' : '遵医嘱用药'
      })
  }
  
  return prescriptions
}

const generateMockDrugs = (count) => {
  const drugNames = [
      '阿莫西林胶囊',
      '布洛芬缓释片',
      '头孢克肟分散片',
      '蒙脱石散',
      '维生素C片',
      '复方甘草片',
      '盐酸左氧氟沙星片',
      '板蓝根颗粒'
  ]
  
  const drugs = []
  for (let i = 0; i < count; i++) {
      const name = drugNames[Math.floor(Math.random() * drugNames.length)]
      drugs.push({
          id: `D${100 + i}`,
          name: name,
          specification: ['0.25g*24片', '10mg*12粒', '50mg*6袋', '100ml/瓶'][Math.floor(Math.random() * 4)],
          dosage: ['1片', '2粒', '1袋', '10ml'][Math.floor(Math.random() * 4)],
          frequency: ['每日一次', '每日两次', '每日三次', '每四小时一次'][Math.floor(Math.random() * 4)],
          quantity: Math.floor(Math.random() * 3) + 1,
          price: parseFloat((Math.random() * 50 + 5).toFixed(2)),
          total: 0
      })
  }
  
  // 计算总价
  drugs.forEach(drug => {
      drug.total = parseFloat((drug.price * drug.quantity).toFixed(2))
  })
  
  return drugs
}

const generateMockPaymentHistory = () => {
  const methods = ['wechat', 'alipay', 'bank']
  const statuses = ['success', 'success', 'success', 'fail'] // 大部分成功
  
  const history = []
  for (let i = 1; i <= 10; i++) {
      const daysOffset = Math.floor(Math.random() * 60) // 0到60天前
      const status = statuses[Math.floor(Math.random() * statuses.length)]
      
      history.push({
          id: `PAY${2000 + i}`,
          date: dayjs().subtract(daysOffset, 'day').format('YYYY-MM-DD HH:mm:ss'),
          method: methods[Math.floor(Math.random() * methods.length)],
          amount: parseFloat((Math.random() * 300 + 50).toFixed(2)),
          status: status,
          transactionId: `T${Math.floor(Math.random() * 1000000000)}`,
          prescriptions: generateMockPrescriptions().slice(0, Math.floor(Math.random() * 3) + 1)
      })
  }
  
  return history.sort((a, b) => dayjs(b.date).valueOf() - dayjs(a.date).valueOf())
}

const viewPrescriptionDetail = (prescription) => {
  currentPrescription.value = prescription
  prescriptionDetailVisible.value = true
}

const viewPaymentDetail = (payment) => {
  currentPayment.value = payment
  paymentDetailVisible.value = true
}

const handlePayment = async () => {
  if (selectedPrescriptions.value.length === 0) {
      message.warning('请选择要缴费的处方')
      return
  }
  
  try {
      paying.value = true
      for (const presId of selectedPrescriptionIds.value) {
          const response = await payPres(presId)
          if (response.data.code === 200) {
              const index = prescriptions.value.findIndex(p => p.pres_id === presId)
              if (index!== -1) {
                  prescriptions.value[index].status_code = 1
              }
          } else {
              message.error('处方缴费失败')
              return
          }
      }

      // 模拟支付结果 (80%成功率)
      const success = Math.random() > 0.2
      
      paymentResult.value = {
          success: success,
          amount: actualAmount.value,
          method: paymentMethod.value,
          transactionId: `T${Math.floor(Math.random() * 1000000000)}`,
          time: dayjs().format('YYYY-MM-DD HH:mm:ss'),
          message: success ? '' : '支付超时，请重试'
      }
      
      if (success) {
          // 更新处方状态
          selectedPrescriptionIds.value.forEach(id => {
              const index = prescriptions.value.findIndex(p => p.pres_id === id)
              if (index!== -1) {
                  prescriptions.value[index].status_code = 1
              }
          })
          
          // 添加到缴费历史
          paymentHistory.value.unshift({
              id: `PAY${2000 + paymentHistory.value.length + 1}`,
              date: paymentResult.value.time,
              method: paymentMethod.value,
              amount: actualAmount.value,
              status: 'success',
              transactionId: paymentResult.value.transactionId,
              prescriptions: [...selectedPrescriptions.value]
          })
          
          // 清空选择
          selectedPrescriptionIds.value = []
          selectedCoupon.value = null
      }
      
      paymentResultVisible.value = true
  } catch (error) {
      message.error('支付处理失败' + error.message)
  } finally {
      paying.value = false
  }
}

const handlePaymentSuccess = () => {
  paymentResultVisible.value = false
  // 可以跳转到处方详情或其他页面
}

const downloadInvoice = (payment) => {
  message.success('正在生成发票，请稍候...')
  // 模拟下载延迟
  setTimeout(() => {
      message.success(`发票 ${payment.id} 下载成功`)
  }, 1500)
}

const fetchCheckupRecords = async () => {
    try {
        loadingCheckups.value = true;
        const loginInfo = await getLoginInfo();
        const patientId = loginInfo.data.data.user_id;
        const appointInfo = await getAppoint({ patient_id: patientId });
        const appointIds = appointInfo.data.data.map(item => item.app_if.AppointmentID);
        const allCheckupRecords = [];

        for (const appointId of appointIds) {
            try {
                const checkupData = await getCheckupRecord(appointId);
                // 增加对 checkupData.data.data 是否为 null 或 undefined 的判断
                if (checkupData.data.data && checkupData.data.data.length > 0) {
                    allCheckupRecords.push(...checkupData.data.data);
                }
            } catch (error) {
                console.error(`获取挂号 ${appointId} 的检查记录失败:`, error);
            }
        }

        checkupRecords.value = allCheckupRecords;

        if (allCheckupRecords.length === 0) {
            message.info('未找到任何检查记录');
        }
    } catch (error) {
        message.error('获取检查记录失败：' + error.message);
    } finally {
        loadingCheckups.value = false;
    }
};

const payForCheckup = async examId => {
  try {
      const response = await payCheckup(examId);
      if (response.data.code === 200) {
          const index = checkupRecords.value.findIndex(record => record.exam_id === examId);
          if (index!== -1) {
              checkupRecords.value[index].status_code = 1;
              checkupRecords.value[index].oper_time = new Date().toLocaleString();
          }
          message.success('检查记录付款成功');
      } else {
          message.error('检查记录付款失败');
      }
  } catch (error) {
      message.error('检查记录付款失败：' + error.message);
  }
}

const showCheckupResultModal = async record => {
  currentCheckupRecord.value = record;
  try {
      const response = await getCheckupResult(record.exam_id);
      if (response.data.code === 200) {
          currentCheckupResult.value = response.data.data;
      } else {
          currentCheckupResult.value = null;
      }
  } catch (error) {
      currentCheckupResult.value = null;
  }
  checkupResultModalVisible.value = true;
}

const hideCheckupResultModal = () => {
  checkupResultModalVisible.value = false;
  currentCheckupResult.value = null;
}

const fetchAllExams = async () => {
  try {
    const response = await getCheckup({});
    if (response.data.code === 200) {
      allExams.value = response.data.data;
      examMap.value = allExams.value.reduce((map, exam) => {
        map[exam.exam_def_id] = exam;
        return map;
      }, {});
    }
  } catch (error) {
    message.error('获取检查项目数据失败');
  }
};

// 初始化加载数据
onMounted(() => {
  fetchPrescriptions();
  fetchPaymentHistory();
  fetchAvailableCoupons();
  fetchCheckupRecords();
  fetchAllExams();
});
</script>

<style scoped>
.payment-container {
  padding: 24px;
}

.section {
  margin-bottom: 32px;
}

.section-title {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 16px;
  color: rgba(0, 0, 0, 0.85);
}

.total-amount {
  font-weight: 500;
  color: #f5222d;
}

.actual-amount {
  font-weight: 600;
  font-size: 18px;
  color: #f5222d;
}

.payment-method {
  display: flex;
  align-items: center;
  padding: 8px;
}

.coupon-section {
  margin: 16px 0;
  padding: 16px;
  background: #fafafa;
  border-radius: 4px;
}

.coupon-section h4 {
  margin-bottom: 12px;
  font-weight: 500;
}

.payment-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
}

.payment-result {
  text-align: center;
  padding: 16px;
}

.payment-result h3 {
  margin: 16px 0;
  font-size: 20px;
}

.payment-result p {
  margin: 8px 0;
  color: rgba(0, 0, 0, 0.65);
}

.result-actions {
  margin-top: 24px;
  display: flex;
  justify-content: center;
  gap: 16px;
}
</style>        