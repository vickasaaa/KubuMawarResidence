<script setup lang="ts">
// Privacy Policy Page - Styled after Rukita's minimalist layout
</script>

<template>
  <div class="rukita-privacy-root">
    <!-- Top Centered Logo -->
    <header class="rukita-privacy-header">
      <div class="rukita-brand-center">
        <img src="/images/logo.png" alt="Kubu Mawar" class="rukita-logo-img" />
        <span class="rukita-brand-text">Kubu Mawar</span>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="rukita-privacy-content">
      <h1 class="rukita-title">Privacy Policy</h1>

      <p class="rukita-p">
        We created this website to provide services that can be accessed by users. This SERVICE is provided and intended to be used in accordance with the terms and conditions that apply.
      </p>

      <p class="rukita-p">
        This page is used to inform visitors about our policies regarding the collection, use, and disclosure of Personal Information if someone decides to use our Services.
      </p>

      <p class="rukita-p">
        If you choose to use our Services, then you agree to the collection and use of information in accordance with this policy. The Personal Information that we collect will be used to provide and improve the Services. We will not use or share your Personal Information with anyone, except as described in this Privacy Policy.
      </p>

      <p class="rukita-p">
        The terms used in this Privacy Policy have the same meanings as in our Terms and Conditions, which can be accessed on the Kubu Mawar Website unless otherwise defined in this Privacy Policy.
      </p>

      <h2 class="rukita-heading">Collection and Use of Information</h2>

      <p class="rukita-p">
        For a better experience, while using our Services, Kubu Mawar may request you to provide us with certain personally identifiable information, including but not limited to Name, Email, Phone Number, and Date of Birth. The requested information will be retained by us and used as described in this Privacy Policy.
      </p>

      <p class="rukita-p">
        This website uses third-party services that can collect information used to identify you. Link to the Privacy Policy of third-party service providers used by the website:
      </p>

      <p class="rukita-p">
        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="rukita-link">Google Analytics</a>
      </p>

      <h2 class="rukita-heading">Log Data</h2>

      <p class="rukita-p">
        We want to inform you that whenever you visit our Service, we collect information that your browser sends to us that is called Log Data. This Log Data may include information such as your computer's Internet Protocol ("IP") address, browser version, pages of our Service that you visit, the time and date of your visit, the time spent on those pages, and other statistics.
      </p>

      <h2 class="rukita-heading">Cookies</h2>

      <p class="rukita-p">
        Cookies are files with a small amount of data that are commonly used as anonymous unique identifiers. These are sent to your browser from the websites that you visit and are stored on your computer's hard drive.
      </p>

      <p class="rukita-p">
        Our website uses these "cookies" to collect information and improve our Services. You have the option to either accept or refuse these cookies and know when a cookie is being sent to your computer. If you choose to refuse our cookies, you may not be able to use some portions of our Service.
      </p>

      <h2 class="rukita-heading">Service Providers</h2>

      <p class="rukita-p">
        We may employ third-party companies and individuals due to the following reasons:
      </p>

      <ul class="rukita-list">
        <li>To facilitate our Service;</li>
        <li>To provide the Service on our behalf;</li>
        <li>To perform Service-related services; or</li>
        <li>To assist us in analyzing how our Service is used.</li>
      </ul>

      <p class="rukita-p">
        We want to inform our Service users that these third parties have access to your Personal Information. The reason is to perform the tasks assigned to them on our behalf. However, they are obligated not to disclose or use the information for any other purpose.
      </p>

      <h2 class="rukita-heading">Security</h2>

      <p class="rukita-p">
        We value your trust in providing us your Personal Information, thus we are striving to use commercially acceptable means of protecting it. But remember that no method of transmission over the internet, or method of electronic storage is 100% secure and reliable, and we cannot guarantee its absolute security.
      </p>

      <h2 class="rukita-heading">Links to Other Sites</h2>

      <p class="rukita-p">
        Our Service may contain links to other sites. If you click on a third-party link, you will be directed to that site. Note that these external sites are not operated by us. Therefore, we strongly advise you to review the Privacy Policy of these websites. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites or services.
      </p>

      <h2 class="rukita-heading">Children's Privacy</h2>

      <p class="rukita-p">
        Our Services do not address anyone under the age of 13. We do not knowingly collect personally identifiable information from children under 13. In the case we discover that a child under 13 has provided us with personal information, we immediately delete this from our servers. If you are a parent or guardian and you are aware that your child has provided us with personal information, please contact us so that we will be able to do necessary actions.
      </p>

      <h2 class="rukita-heading">Changes to This Privacy Policy</h2>

      <p class="rukita-p">
        We may update our Privacy Policy from time to time. Thus, we advise you to review this page periodically for any changes. We will notify you of any changes by posting the new Privacy Policy on this page. These changes are effective immediately after they are posted on this page.
      </p>

      <h2 class="rukita-heading">Contact Us</h2>

      <p class="rukita-p">
        If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us at <a href="mailto:hello@kubumawar.com" class="rukita-link">kubumawarresidence@gmail.com</a> or via WhatsApp at <a href="https://wa.me/6281991888008" target="_blank" rel="noopener noreferrer" class="rukita-link">081991888008</a>.
      </p>
    </main>
  </div>
</template>

<style scoped>
.rukita-privacy-root {
  background-color: #ffffff;
  min-height: 100vh;
  color: #212529;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

/* Centered Top Logo Header */
.rukita-privacy-header {
  padding: 44px 20px 24px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.rukita-brand-center {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.rukita-logo-img {
  width: 38px;
  height: 38px;
  object-fit: contain;
}

.rukita-brand-text {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

/* Content Container */
.rukita-privacy-content {
  max-width: 780px;
  margin: 0 auto;
  padding: 24px 24px 96px;
}

/* Headings */
.rukita-title {
  font-size: 1.65rem;
  font-weight: 700;
  color: #111827;
  margin-top: 16px;
  margin-bottom: 24px;
  letter-spacing: -0.01em;
}

.rukita-heading {
  font-size: 1.15rem;
  font-weight: 700;
  color: #111827;
  margin-top: 32px;
  margin-bottom: 14px;
}

/* Paragraphs & Lists */
.rukita-p {
  font-size: 0.94rem;
  line-height: 1.65;
  color: #374151;
  margin-bottom: 16px;
}

.rukita-list {
  margin: 0 0 16px 20px;
  padding: 0;
  color: #374151;
  font-size: 0.94rem;
  line-height: 1.65;
}

.rukita-list li {
  margin-bottom: 6px;
}

/* Links */
.rukita-link {
  color: #111827;
  text-decoration: underline;
  font-weight: 500;
  transition: color 0.15s ease;
}

.rukita-link:hover {
  color: #C8102E;
}

@media (max-width: 640px) {
  .rukita-privacy-header {
    padding: 32px 16px 16px;
  }
  .rukita-title {
    font-size: 1.45rem;
  }
  .rukita-privacy-content {
    padding: 16px 20px 64px;
  }
}
</style>
