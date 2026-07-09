import { Component } from '@angular/core';

@Component({
  selector: 'app-about-page',
  standalone: true,
  template: `
    <section class="panel about-page">
      <p class="eyebrow">About Us</p>
      <h1>Namelenam</h1>

      <div class="about-copy">
        <p>
          Welcome to <strong>Namelenam</strong>, where beauty, innovation, and quality come together to create premium
          cosmetic and personal care products. Based in the United States, Namelenam is a modern cosmetics
          manufacturing company dedicated to developing safe, effective, and high-quality products that enhance
          everyday beauty and well-being.
        </p>
        <p>
          Since our establishment, our mission has been to deliver exceptional skincare, haircare, and personal hygiene
          solutions that combine scientific research, advanced manufacturing technologies, and carefully selected
          ingredients. Every product we manufacture reflects our commitment to excellence, customer satisfaction, and
          continuous innovation.
        </p>
        <p>
          At Namelenam, we believe that beauty begins with quality. Our manufacturing facility is equipped with
          state-of-the-art production lines, automated filling systems, and advanced laboratory equipment that ensure
          every product is produced with precision and consistency. Our experienced team of chemists, cosmetic
          scientists, quality control specialists, and production professionals work together to develop formulas that
          meet the evolving needs of consumers worldwide.
        </p>
        <p>
          Our extensive product portfolio includes facial cleansers, moisturizing creams, anti-aging skincare products,
          facial serums, body lotions, shampoos, conditioners, hair treatment products, liquid soaps, hand sanitizers,
          deodorants, body washes, cosmetic creams, makeup products, and a wide variety of personal hygiene essentials.
          Every formulation is carefully designed to provide outstanding performance while remaining gentle and safe for
          everyday use.
        </p>
        <p>
          Quality assurance is at the heart of everything we do. Every stage of our manufacturing process is monitored
          through strict quality control procedures to ensure product safety, consistency, and reliability. We source
          premium raw materials from trusted suppliers and continuously evaluate our production processes to maintain
          the highest standards of excellence. Our commitment to quality enables us to produce products that meet the
          expectations of customers across domestic and international markets.
        </p>
        <p>
          Innovation drives our success. Our Research and Development department constantly explores new technologies,
          advanced cosmetic ingredients, and sustainable solutions to create products that address modern consumer
          needs. By staying ahead of industry trends, we ensure that Namelenam continues to offer innovative solutions
          that combine performance, comfort, and elegance.
        </p>
        <p>
          Environmental responsibility is one of our core values. We recognize the importance of protecting natural
          resources and minimizing our environmental impact. Our company actively invests in environmentally friendly
          manufacturing practices, energy-efficient production systems, recyclable packaging materials, and responsible
          sourcing strategies. We believe that sustainable manufacturing is essential for building a healthier future
          for generations to come.
        </p>
        <p>
          Namelenam also provides comprehensive private label and contract manufacturing services for businesses seeking
          reliable production partners. From concept development and custom formulation to packaging design,
          manufacturing, labeling, quality assurance, and logistics support, our experienced team helps brands transform
          innovative ideas into successful products. Whether you are launching a new cosmetic brand or expanding an
          existing product line, we are committed to delivering flexible manufacturing solutions tailored to your
          business objectives.
        </p>
        <p>
          Customer satisfaction remains our highest priority. We strive to build long-term partnerships based on trust,
          transparency, consistency, and exceptional service. Our commitment extends beyond manufacturing by providing
          professional support, reliable communication, and timely product delivery to every customer and business
          partner.
        </p>
        <p>
          As we continue to grow, Namelenam remains dedicated to producing cosmetic and personal care products that
          improve everyday life through quality, innovation, and integrity. Our vision is to become a globally
          recognized manufacturer known for excellence, reliability, and sustainable business practices while helping
          customers feel confident in every product they use.
        </p>
        <p>
          We sincerely appreciate your trust in Namelenam and look forward to serving individuals, retailers,
          distributors, and business partners around the world with products that inspire confidence, beauty, and
          wellness.
        </p>
      </div>

      <section class="contact-section">
        <h2>Contact Information</h2>
        <h3>Namelenam Cosmetics Manufacturing</h3>

        <dl class="detail-list">
          <div>
            <dt>Address</dt>
            <dd>
              4587 West Innovation Boulevard<br>
              Irvine, California 92618<br>
              United States
            </dd>
          </div>
          <div>
            <dt>Phone</dt>
            <dd><a href="tel:+19495553816">+1 (949) 555-3816</a></dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd><a href="mailto:info@namelenam.com">info@namelenam.com</a></dd>
          </div>
          <div>
            <dt>Website</dt>
            <dd><a href="http://www.namelenam.com/" target="_blank" rel="noopener">www.namelenam.com</a></dd>
          </div>
        </dl>
      </section>
    </section>
  `
})
export class AboutPageComponent {}
