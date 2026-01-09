<template>
  <div class="codecheck-review-display">
    <h2 class="codecheck-information-heading">{{ t("plugins.generic.codecheck.reviewTitle") }}</h2>
    
    <div v-if="submission?.codecheckOptIn" class="codecheck-info">
      <div class="codecheck-status-box">
        <h3>{{ t("plugins.generic.codecheck.status") }}</h3>
        <p class="status-badge" :class="statusClass">
          {{ getStatusText() }}
        </p>
      </div>

      <div class="codecheck-status-box" v-if="hasMetadata && metadata.configVersion">
        <h3>{{ t("plugins.generic.codecheck.review.configVersion") }}</h3>
        <p>{{ metadata.configVersion }}</p>
      </div>

      <div class="codecheck-status-box" v-if="hasMetadata && metadata.publicationType">
        <h3>{{ t("plugins.generic.codecheck.review.publicationType") }}</h3>
        <p>{{ metadata.publicationType === 'doi' 
              ? t("plugins.generic.codecheck.review.publicationType.doi") 
              : t("plugins.generic.codecheck.review.publicationType.separate") }}</p>
      </div>
      
      <div class="codecheck-status-box" v-if="hasMetadata && metadata.certificate">
        <h3>{{ t("plugins.generic.codecheck.identifier.label") }}</h3>
        <p>{{ metadata.certificate }}</p>
      </div>

      <div class="codecheck-status-box" v-if="hasMetadata && metadata.manifest && metadata.manifest.length > 0">
        <h3>{{ t("plugins.generic.codecheck.review.manifestFiles") }}</h3>
        <ul>
          <li v-for="(file, index) in metadata.manifest" :key="index">
            <strong>{{ file.file }}</strong>
            <span v-if="file.comment"> - {{ file.comment }}</span>
          </li>
        </ul>
      </div>

      <div class="codecheck-status-box" v-if="hasMetadata && metadata.codecheckers && metadata.codecheckers.length > 0">
        <h3>{{ t("plugins.generic.codecheck.review.codecheckers") }}</h3>
        <ul>
          <li v-for="(checker, index) in metadata.codecheckers" :key="index">
            {{ checker.name }}
            <span v-if="checker.orcid" class="orcid-badge">{{ checker.orcid }}</span>
          </li>
        </ul>
      </div>
      
      <div class="codecheck-status-box" v-if="hasMetadata && metadata.repository">
        <h3>{{ t("plugins.generic.codecheck.repositories.title") }}</h3>
        <a :href="metadata.repository" target="_blank">{{ metadata.repository }}</a>
      </div>
      
      <div class="codecheck-status-box" v-if="hasMetadata && metadata.checkTime">
        <h3>{{ t("plugins.generic.codecheck.completionTime.label") }}</h3>
        <p>{{ formatDate(metadata.checkTime) }}</p>
      </div>
      
      <div class="codecheck-status-box" v-if="hasMetadata && metadata.summary">
        <h3>{{ t("plugins.generic.codecheck.certificate.summary") }}</h3>
        <p>{{ metadata.summary }}</p>
      </div>

      <div class="codecheck-status-box" v-if="hasMetadata && metadata.reportUrl">
        <h3>{{ t("plugins.generic.codecheck.review.reportUrl") }}</h3>
        <a :href="metadata.reportUrl" target="_blank">{{ metadata.reportUrl }}</a>
      </div>
      
      <div class="actions">
        <pkp-button @click="viewFullMetadata">
          {{ t("plugins.generic.codecheck.viewFullMetadata") }}
        </pkp-button>
      </div>
    </div>
    
    <div v-else class="codecheck-not-opted">
      <p>{{ t("plugins.generic.codecheck.notOptedIn") }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

const props = defineProps({
  submission: { type: Object, required: true }
});

const metadata = computed(() => {
  if (props.submission.codecheckMetadata) {
    if (typeof props.submission.codecheckMetadata === 'string') {
      try {
        return JSON.parse(props.submission.codecheckMetadata);
      } catch (e) {
        console.error('Failed to parse codecheck metadata:', e);
        return {};
      }
    }
    return props.submission.codecheckMetadata;
  }
  return {};
});

const hasMetadata = computed(() => {
  return Object.keys(metadata.value).length > 0;
});

function getStatus() {
  if (metadata.value.certificate && metadata.value.checkTime) {
    return 'complete';
  } else if (hasMetadata.value) {
    return 'in-progress';
  }
  return 'pending';
}

const statusClass = computed(() => {
  const status = getStatus();
  switch (status) {
    case 'complete':
      return 'status-complete';
    case 'in-progress':
      return 'status-in-progress';
    case 'pending':
    default:
      return 'status-pending';
  }
});

function getStatusText() {
  const status = getStatus();
  switch (status) {
    case 'complete':
      return t("plugins.generic.codecheck.status.complete");
    case 'in-progress':
      return t("plugins.generic.codecheck.status.inProgress");
    case 'pending':
    default:
      return t("plugins.generic.codecheck.status.pending");
  }
}

function formatDate(dateString) {
  if (!dateString) return '';
  let date = new Date(dateString);
  return date.toLocaleString();
}

function viewFullMetadata() {
  const workflowStore = pkp.registry.getPiniaStore("workflow");
  workflowStore.selectedMenuState = {
    primaryMenuItem: 'workflow',
    stageId: 999
  };
}
</script>

<style scoped>
.codecheck-review-display {
  background: white;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  margin-bottom: var(--spacing-4);
  font-size: 16px;
}

.codecheck-review-display .codecheck-information-heading {
  margin: 0 0 var(--spacing-4) 0;
  font: var(--font-xl-bold);
  font-size: 1.25rem !important;
  color: var(--text-color-heading);
}

.codecheck-info {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
}

.codecheck-status-box {
  padding: 1rem;
  --tw-border-opacity: 1;
  border-color: rgb(187 187 187 / var(--tw-border-opacity, 1));
  border-width: 1px;
  box-sizing: border-box;
  border-style: solid;
  pointer-events: auto;
  color: rgba(0,0,0,0.84);
}

.codecheck-status-box h3 {
  --tw-text-opacity: 1;
  color: rgb(1 53 79 / var(--tw-text-opacity, 1));
  font-size: 0.875rem;
  line-height: 1.25rem;
  font-weight: 700;
  margin: 0;
  margin-bottom: .5rem;
  box-sizing: border-box;
  pointer-events: auto;
  font-family: var(--font-sans);
}

.codecheck-status-box p {
  margin: 0;
  font-size: 0.6875rem;
  line-height: .875rem;
  font-weight: 400;
  color: rgba(0,0,0,0.84);
}

.codecheck-status-box ul {
  margin: 0;
  padding-left: var(--spacing-4);
}

.codecheck-status-box a {
  color: var(--color-primary);
  text-decoration: none;
}

.codecheck-status-box a:hover {
  text-decoration: underline;
}

.status-badge {
  padding: 0;
}

.status-complete {
  background: var(--color-success-light);
  color: var(--color-success);
}

.status-in-progress {
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.status-pending {
  background: var(--color-background-light);
  color: var(--text-color-secondary);
}

.orcid-badge {
  margin-left: 0.5rem;
  padding: 0.25rem 0.5rem;
  background: #a6ce39;
  color: white;
  font-size: 11px;
  border-radius: 3px;
  font-weight: 600;
}

.actions {
  margin-top: var(--spacing-4);
  padding-top: var(--spacing-4);
  border-top: 1px solid var(--color-border);
}

.codecheck-not-opted {
  padding: var(--spacing-3);
  background: var(--color-background-light);
  border-radius: 4px;
  color: var(--text-color-secondary);
}
</style>