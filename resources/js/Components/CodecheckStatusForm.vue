<template>
    <div class="codecheck-status-form border border-light">
        <div class="flex items-center justify-between bg-default p-5">
            <h3 class="text-2xl-bold uppercase text-heading">Status</h3>
            <div class="flex gap-x-2">
                <button
                    class="
                        pkpButton
                        inline-flex
                        relative
                        items-center
                        gap-x-1 
                        text-lg-semibold
                        text-primary
                        border-light 
                        hover:text-hover
                        disabled:text-disabled 
                        bg-secondary
                        py-[0.4375rem]
                        px-3
                        border
                        rounded
                    "
                    type="button"
                    href="false"
                >
                    Change
                </button>
            </div>
        </div>
        <div v-if="loading" class="loading-state">
            <span class="pkpSpinner"></span>
            <p>{{ t('common.loading') }}</p>
        </div>

        <div v-else-if="error" class="error-state">
            <p>{{ error }}</p>
            <button class="pkpButton" @click="loadData">{{ t('common.retry') }}</button>
        </div>

        <div v-else-if="dataLoaded">
            <div v-if="submission?.codecheckOptIn" class="codecheck-info">
                <div class="border-light border-t p-4">
                    <p class="text-base-normal" :class="statusClass">
                        {{ getStatusText() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const { useLocalize } = pkp.modules.useLocalize;

export default {
  name: 'CodecheckStatusForm',
  props: {
    submission: { type: Object, required: true },
    canEdit: { type: Boolean, default: true },
    name: {type: String},
    value: {type: String},
  },
  setup() {
    const { t } = useLocalize();
    return { t };
  },
  data() {
    return {
      loading: true,
      saving: false,
      dataLoaded: false,
      error: null,
      saveMessage: '',
      saveMessageType: '',
      hasUnsavedChanges: false,
      statusData: [],
    }
  },
  computed: {
    
  },
  mounted() {
    this.loadStatusData();
  },
  watch: {
    metadata: {
      handler() {
        this.hasUnsavedChanges = true;
      },
      deep: true
    },
    repositories: {
      handler() {
        this.hasUnsavedChanges = true;
      },
      deep: true
    }
  },
  methods: {
    async loadStatusData() {
        console.log('Loading the status Data', this.submission.id);
        try {
            if (!this.submission?.id) return;

            const submissionId = this.submission.id;
            const apiUrl = `${pkp.context.apiBaseUrl}codecheck/status?submissionId=${submissionId}`;
            
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: { 'X-Csrf-Token': pkp.currentUser.csrfToken }
            });

            const data = await response.json();
            this.statusData = data.statusRecord;
            
            this.dataLoaded = true;
        } catch (error) {
            console.error('getStatus error:', error);
        } finally {
            this.loading = false;
        }
    },
    getStatusText() {
        return this.t(this.statusData.status);
    }
  }
}
</script>

<style>
</style>