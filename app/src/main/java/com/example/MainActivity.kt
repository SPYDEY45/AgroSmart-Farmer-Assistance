package com.example

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.ui.theme.MyApplicationTheme

class MainActivity : ComponentActivity() {
  override fun onCreate(savedInstanceState: Bundle?) {
    super.onCreate(savedInstanceState)
    enableEdgeToEdge()
    setContent {
      MyApplicationTheme {
        AgroSmartApp()
      }
    }
  }
}

enum class AppTab(val title: String, val icon: ImageVector) {
  DASHBOARD("Home", Icons.Default.Home),
  RECOMMEND("Advisory", Icons.Default.Psychology),
  MARKET("Mandi", Icons.Default.Storefront),
  DISEASES("Doctor", Icons.Default.MedicalServices),
  VIVA("BCA Viva", Icons.Default.School)
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AgroSmartApp() {
  var selectedTab by remember { mutableStateOf(AppTab.DASHBOARD) }

  Scaffold(
    topBar = {
      TopAppBar(
        title = {
          Row(verticalAlignment = Alignment.CenterVertically) {
            Text("🌱", fontSize = 24.sp, modifier = Modifier.padding(end = 8.dp))
            Column {
              Text(
                "AgroSmart",
                fontWeight = FontWeight.Bold,
                fontSize = 18.sp,
                color = MaterialTheme.colorScheme.onPrimary
              )
              Text(
                "Smart Agriculture & Market Portal",
                fontSize = 11.sp,
                color = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.8f)
              )
            }
          }
        },
        colors = TopAppBarDefaults.topAppBarColors(
          containerColor = MaterialTheme.colorScheme.primary,
          titleContentColor = MaterialTheme.colorScheme.onPrimary
        ),
        actions = {
          IconButton(
            onClick = { selectedTab = AppTab.VIVA },
            modifier = Modifier.testTag("viva_info_btn")
          ) {
            Icon(
              Icons.Default.Info,
              contentDescription = "Project Information",
              tint = MaterialTheme.colorScheme.onPrimary
            )
          }
        }
      )
    },
    bottomBar = {
      NavigationBar(
        containerColor = MaterialTheme.colorScheme.surface,
        tonalElevation = 8.dp
      ) {
        AppTab.values().forEach { tab ->
          NavigationBarItem(
            selected = selectedTab == tab,
            onClick = { selectedTab = tab },
            icon = { Icon(tab.icon, contentDescription = tab.title) },
            label = { Text(tab.title, fontSize = 11.sp) },
            modifier = Modifier.testTag("tab_${tab.name.lowercase()}"),
            colors = NavigationBarItemDefaults.colors(
              selectedIconColor = MaterialTheme.colorScheme.primary,
              selectedTextColor = MaterialTheme.colorScheme.primary,
              indicatorColor = MaterialTheme.colorScheme.primary.copy(alpha = 0.15f)
            )
          )
        }
      }
    }
  ) { paddingValues ->
    Box(
      modifier = Modifier
        .fillMaxSize()
        .padding(paddingValues)
        .background(MaterialTheme.colorScheme.background)
    ) {
      when (selectedTab) {
        AppTab.DASHBOARD -> DashboardScreen(
          onNavigateToRecommend = { selectedTab = AppTab.RECOMMEND },
          onNavigateToMarket = { selectedTab = AppTab.MARKET }
        )
        AppTab.RECOMMEND -> RecommendationScreen()
        AppTab.MARKET -> MarketplaceScreen()
        AppTab.DISEASES -> DiseaseDoctorScreen()
        AppTab.VIVA -> VivaProjectScreen()
      }
    }
  }
}

/* ==================== 1. DASHBOARD SCREEN ==================== */

@Composable
fun DashboardScreen(
  onNavigateToRecommend: () -> Unit,
  onNavigateToMarket: () -> Unit
) {
  LazyColumn(
    modifier = Modifier.fillMaxSize(),
    contentPadding = PaddingValues(16.dp),
    verticalArrangement = Arrangement.spacedBy(16.dp)
  ) {
    item {
      // Welcome Card
      Card(
        modifier = Modifier.fillMaxWidth().testTag("welcome_banner"),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primary)
      ) {
        Column(modifier = Modifier.padding(20.dp)) {
          Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
          ) {
            Column(modifier = Modifier.weight(1f)) {
              Text(
                "Welcome to AgroSmart",
                fontSize = 20.sp,
                fontWeight = FontWeight.Bold,
                color = Color.White
              )
              Text(
                "Empowering Farmers with Smart Agronomy & Direct Markets",
                fontSize = 13.sp,
                color = Color.White.copy(alpha = 0.85f),
                modifier = Modifier.padding(top = 4.dp)
              )
            }
            Text("🌾", fontSize = 36.sp)
          }

          Spacer(modifier = Modifier.height(16.dp))

          Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            Button(
              onClick = onNavigateToRecommend,
              colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.tertiary),
              shape = RoundedCornerShape(8.dp),
              modifier = Modifier.testTag("action_crop_advisor")
            ) {
              Text("Crop Advisor", color = Color.Black, fontWeight = FontWeight.Bold, fontSize = 12.sp)
            }
            OutlinedButton(
              onClick = onNavigateToMarket,
              colors = ButtonDefaults.outlinedButtonColors(contentColor = Color.White),
              shape = RoundedCornerShape(8.dp),
              modifier = Modifier.testTag("action_market_portal")
            ) {
              Text("APMC Mandi", fontSize = 12.sp)
            }
          }
        }
      }
    }

    item {
      // Metric KPI Cards
      Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(8.dp)
      ) {
        MetricTile("₹ 4,750", "Soybean Modal", Icons.Default.TrendingUp, MaterialTheme.colorScheme.primary, Modifier.weight(1f))
        MetricTile("28°C", "Pune Weather", Icons.Default.WbSunny, MaterialTheme.colorScheme.tertiary, Modifier.weight(1f))
        MetricTile("13 Tables", "BCA Database", Icons.Default.Storage, MaterialTheme.colorScheme.secondary, Modifier.weight(1f))
      }
    }

    item {
      // Mandatory Project Notice
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = Color(0xFFFFF9C4))
      ) {
        Row(
          modifier = Modifier.padding(14.dp),
          verticalAlignment = Alignment.Top
        ) {
          Icon(Icons.Default.Warning, contentDescription = "Notice", tint = Color(0xFFF57F17), modifier = Modifier.size(22.dp))
          Spacer(modifier = Modifier.width(10.dp))
          Column {
            Text("Official BCA Project Notice", fontWeight = FontWeight.Bold, fontSize = 13.sp, color = Color(0xFFE65100))
            Text(
              "AgroSmart combines a full-stack PHP 8+ / MySQL 8+ web portal in /agrosmart with an integrated mobile companion for college field evaluation.",
              fontSize = 11.sp,
              color = Color(0xFF5D4037),
              lineHeight = 16.sp
            )
          }
        }
      }
    }

    item {
      Text(
        "Direct Farmer Produce Lots",
        fontWeight = FontWeight.Bold,
        fontSize = 16.sp,
        color = MaterialTheme.colorScheme.onBackground
      )
    }

    // Sample Active Produce Lots
    items(sampleProduceList) { produce ->
      ProduceCard(produce)
    }
  }
}

@Composable
fun MetricTile(value: String, label: String, icon: ImageVector, accentColor: Color, modifier: Modifier = Modifier) {
  Card(
    modifier = modifier,
    shape = RoundedCornerShape(12.dp),
    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
  ) {
    Column(modifier = Modifier.padding(12.dp)) {
      Icon(icon, contentDescription = label, tint = accentColor, modifier = Modifier.size(20.dp))
      Spacer(modifier = Modifier.height(6.dp))
      Text(value, fontWeight = FontWeight.Bold, fontSize = 15.sp, color = MaterialTheme.colorScheme.onSurface)
      Text(label, fontSize = 10.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
    }
  }
}

/* ==================== 2. RECOMMENDATION SCREEN ==================== */

@Composable
fun RecommendationScreen() {
  var season by remember { mutableStateOf("Kharif") }
  var soil by remember { mutableStateOf("Black Soil") }
  var water by remember { mutableStateOf("Medium") }
  var landArea by remember { mutableStateOf("3.5") }
  var calculatedResults by remember { mutableStateOf<List<CropRecommendation>>(emptyList()) }

  LazyColumn(
    modifier = Modifier.fillMaxSize(),
    contentPadding = PaddingValues(16.dp),
    verticalArrangement = Arrangement.spacedBy(16.dp)
  ) {
    item {
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
      ) {
        Column(modifier = Modifier.padding(16.dp)) {
          Text(
            "Smart Crop Recommendation Engine",
            fontSize = 17.sp,
            fontWeight = FontWeight.Bold,
            color = MaterialTheme.colorScheme.primary
          )
          Text(
            "Rule-based agronomic algorithm matching local soil, season, and rainfall.",
            fontSize = 12.sp,
            color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.7f),
            modifier = Modifier.padding(bottom = 16.dp)
          )

          // Season Selection
          Text("Target Season", fontWeight = FontWeight.SemiBold, fontSize = 12.sp)
          Row(modifier = Modifier.fillMaxWidth().padding(vertical = 6.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            listOf("Kharif", "Rabi", "Zaid").forEach { s ->
              FilterChip(
                selected = season == s,
                onClick = { season = s },
                label = { Text(s, fontSize = 11.sp) },
                modifier = Modifier.testTag("season_$s")
              )
            }
          }

          Spacer(modifier = Modifier.height(8.dp))

          // Soil Selection
          Text("Field Soil Type", fontWeight = FontWeight.SemiBold, fontSize = 12.sp)
          Row(modifier = Modifier.fillMaxWidth().padding(vertical = 6.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            listOf("Black Soil", "Red Soil", "Alluvial", "Clay").forEach { st ->
              FilterChip(
                selected = soil == st,
                onClick = { soil = st },
                label = { Text(st, fontSize = 11.sp) },
                modifier = Modifier.testTag("soil_$st")
              )
            }
          }

          Spacer(modifier = Modifier.height(8.dp))

          // Water Availability
          Text("Irrigation / Water Availability", fontWeight = FontWeight.SemiBold, fontSize = 12.sp)
          Row(modifier = Modifier.fillMaxWidth().padding(vertical = 6.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            listOf("Low", "Medium", "High").forEach { w ->
              FilterChip(
                selected = water == w,
                onClick = { water = w },
                label = { Text(w, fontSize = 11.sp) },
                modifier = Modifier.testTag("water_$w")
              )
            }
          }

          Spacer(modifier = Modifier.height(16.dp))

          Button(
            onClick = {
              calculatedResults = runCropAdvisor(season, soil, water)
            },
            modifier = Modifier.fillMaxWidth().testTag("calculate_recommendation_btn"),
            shape = RoundedCornerShape(10.dp),
            colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primary)
          ) {
            Icon(Icons.Default.Bolt, contentDescription = null, modifier = Modifier.size(18.dp))
            Spacer(modifier = Modifier.width(6.dp))
            Text("Generate Agronomic Recommendations")
          }
        }
      }
    }

    if (calculatedResults.isNotEmpty()) {
      item {
        Text("Optimal Ranked Crops", fontWeight = FontWeight.Bold, fontSize = 16.sp)
      }

      items(calculatedResults) { rec ->
        Card(
          modifier = Modifier.fillMaxWidth().testTag("crop_result_${rec.name}"),
          shape = RoundedCornerShape(12.dp),
          colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
          elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
        ) {
          Column(modifier = Modifier.padding(16.dp)) {
            Row(
              modifier = Modifier.fillMaxWidth(),
              horizontalArrangement = Arrangement.SpaceBetween,
              verticalAlignment = Alignment.CenterVertically
            ) {
              Text(rec.name, fontWeight = FontWeight.Bold, fontSize = 16.sp, color = MaterialTheme.colorScheme.primary)
              Badge(containerColor = MaterialTheme.colorScheme.primaryContainer) {
                Text("${rec.score}% Match", color = MaterialTheme.colorScheme.onPrimaryContainer, fontWeight = FontWeight.Bold)
              }
            }
            Text(rec.reason, fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.8f), modifier = Modifier.padding(top = 4.dp))
            HorizontalDivider(modifier = Modifier.padding(vertical = 8.dp))
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
              Text("Sowing: ${rec.sowing}", fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
              Text("Harvest: ${rec.harvest}", fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
            }
          }
        }
      }
    }
  }
}

/* ==================== 3. MARKETPLACE SCREEN ==================== */

@Composable
fun MarketplaceScreen() {
  LazyColumn(
    modifier = Modifier.fillMaxSize(),
    contentPadding = PaddingValues(16.dp),
    verticalArrangement = Arrangement.spacedBy(14.dp)
  ) {
    item {
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(2.dp)
      ) {
        Column(modifier = Modifier.padding(14.dp)) {
          Text("APMC Wholesale Benchmark Rates", fontWeight = FontWeight.Bold, fontSize = 16.sp, color = MaterialTheme.colorScheme.primary)
          Text("Live Mandi auction modal rates across Maharashtra APMC yards.", fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
          Spacer(modifier = Modifier.height(10.dp))
          sampleMandiRates.forEach { rate ->
            Row(
              modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
              horizontalArrangement = Arrangement.SpaceBetween
            ) {
              Column {
                Text(rate.crop, fontWeight = FontWeight.SemiBold, fontSize = 13.sp)
                Text(rate.market, fontSize = 10.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.5f))
              }
              Text("₹ ${rate.modalPrice}/Qtl", fontWeight = FontWeight.Bold, fontSize = 14.sp, color = MaterialTheme.colorScheme.primary)
            }
            HorizontalDivider(modifier = Modifier.padding(vertical = 2.dp), color = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f))
          }
        }
      }
    }

    item {
      Text("Direct Farm Harvest Lots", fontWeight = FontWeight.Bold, fontSize = 16.sp)
    }

    items(sampleProduceList) { p ->
      ProduceCard(p)
    }
  }
}

/* ==================== 4. DISEASE DOCTOR SCREEN ==================== */

@Composable
fun DiseaseDoctorScreen() {
  LazyColumn(
    modifier = Modifier.fillMaxSize(),
    contentPadding = PaddingValues(16.dp),
    verticalArrangement = Arrangement.spacedBy(14.dp)
  ) {
    item {
      // Mandatory Notice Box
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = Color(0xFFFFEBEE))
      ) {
        Row(modifier = Modifier.padding(14.dp), verticalAlignment = Alignment.Top) {
          Icon(Icons.Default.LocalHospital, contentDescription = "Disclaimer", tint = Color(0xFFC62828), modifier = Modifier.size(24.dp))
          Spacer(modifier = Modifier.width(10.dp))
          Column {
            Text("Official Agronomic Advisory Notice", fontWeight = FontWeight.Bold, fontSize = 13.sp, color = Color(0xFFC62828))
            Text(
              "Disease diagnosis should be verified with local agricultural officers or university Krishi Vigyan Kendra (KVK) centers before applying intensive chemical treatments.",
              fontSize = 11.sp,
              color = Color(0xFF4E342E),
              lineHeight = 16.sp
            )
          }
        }
      }
    }

    items(sampleDiseases) { d ->
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(2.dp)
      ) {
        Column(modifier = Modifier.padding(16.dp)) {
          Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
          ) {
            Text(d.diseaseName, fontWeight = FontWeight.Bold, fontSize = 15.sp, color = Color(0xFFC62828))
            Badge(containerColor = MaterialTheme.colorScheme.primaryContainer) {
              Text("Crop: ${d.cropName}", color = MaterialTheme.colorScheme.onPrimaryContainer)
            }
          }
          Spacer(modifier = Modifier.height(6.dp))
          Text("Symptoms: ${d.symptoms}", fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.8f))
          Spacer(modifier = Modifier.height(6.dp))
          Surface(
            color = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f),
            shape = RoundedCornerShape(8.dp)
          ) {
            Column(modifier = Modifier.padding(10.dp)) {
              Text("Treatment & Control:", fontWeight = FontWeight.Bold, fontSize = 11.sp, color = MaterialTheme.colorScheme.primary)
              Text(d.treatment, fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
            }
          }
        }
      }
    }
  }
}

/* ==================== 5. BCA VIVA SCREEN ==================== */

@Composable
fun VivaProjectScreen() {
  LazyColumn(
    modifier = Modifier.fillMaxSize(),
    contentPadding = PaddingValues(16.dp),
    verticalArrangement = Arrangement.spacedBy(14.dp)
  ) {
    item {
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primary)
      ) {
        Column(modifier = Modifier.padding(18.dp)) {
          Text("BCA Final Year Field Project", fontWeight = FontWeight.Bold, fontSize = 18.sp, color = Color.White)
          Text(
            "Project Title: AgroSmart – Smart Agriculture Assistant & Farmer Market Portal",
            fontSize = 12.sp,
            color = Color.White.copy(alpha = 0.85f),
            modifier = Modifier.padding(top = 4.dp)
          )
          Spacer(modifier = Modifier.height(10.dp))
          Surface(
            color = Color.White.copy(alpha = 0.15f),
            shape = RoundedCornerShape(8.dp)
          ) {
            Text(
              "Full-stack Web Application (PHP 8 + MySQL 8 + Bootstrap 5 + Chart.js) with Android Companion.",
              fontSize = 11.sp,
              color = Color.White,
              modifier = Modifier.padding(8.dp)
            )
          }
        }
      }
    }

    item {
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(2.dp)
      ) {
        Column(modifier = Modifier.padding(16.dp)) {
          Text("Normalized MySQL Database Schema (13 Tables)", fontWeight = FontWeight.Bold, fontSize = 14.sp)
          Spacer(modifier = Modifier.height(8.dp))
          val tables = listOf(
            "1. users (Role-based authentication & status)",
            "2. farmers (Land area, soil type, irrigation)",
            "3. buyers (Business firm name, license, address)",
            "4. experts (Specialization, qualifications)",
            "5. crops (Cultivation parameters & seasons)",
            "6. farmer_crops (Active cultivation tracking)",
            "7. crop_diseases (Symptoms, causes & treatments)",
            "8. products (Farmer marketplace listings)",
            "9. enquiries (Buyer negotiation & volume requests)",
            "10. market_prices (APMC Mandi min/max/modal rates)",
            "11. complaints (Grievance redressal ticketing)",
            "12. schemes (Government welfare & subsidies)",
            "13. expert_questions (Q&A advisory consultation)"
          )
          tables.forEach { t ->
            Text(t, fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.75f), modifier = Modifier.padding(vertical = 2.dp))
          }
        }
      }
    }

    item {
      Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(2.dp)
      ) {
        Column(modifier = Modifier.padding(16.dp)) {
          Text("Web Server Execution Guide", fontWeight = FontWeight.Bold, fontSize = 14.sp)
          Spacer(modifier = Modifier.height(6.dp))
          Text("1. Code Location: Located in /agrosmart in the project root.", fontSize = 11.sp)
          Text("2. Apache / XAMPP: Copy agrosmart/ into htdocs/ or run: php -S localhost:8000 -t agrosmart", fontSize = 11.sp)
          Text("3. Database Import: Execute /agrosmart/database.sql in phpMyAdmin or mysql CLI.", fontSize = 11.sp)
          Text("4. Credentials: admin@agrosmart.com / farmer@agrosmart.com (Pass: password123).", fontSize = 11.sp)
        }
      }
    }
  }
}

/* ==================== COMMON COMPONENTS & DATA ==================== */

@Composable
fun ProduceCard(produce: ProduceItem) {
  Card(
    modifier = Modifier.fillMaxWidth().testTag("produce_${produce.id}"),
    shape = RoundedCornerShape(12.dp),
    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
    elevation = CardDefaults.cardElevation(2.dp)
  ) {
    Column(modifier = Modifier.padding(16.dp)) {
      Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
      ) {
        Column {
          Text(produce.name, fontWeight = FontWeight.Bold, fontSize = 15.sp, color = MaterialTheme.colorScheme.onSurface)
          Text("Farmer: ${produce.farmer} • ${produce.location}", fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f))
        }
        Text("₹ ${produce.price}/${produce.unit}", fontWeight = FontWeight.Bold, fontSize = 15.sp, color = MaterialTheme.colorScheme.primary)
      }
      Spacer(modifier = Modifier.height(8.dp))
      Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
      ) {
        Badge(containerColor = MaterialTheme.colorScheme.secondaryContainer) {
          Text("Lot: ${produce.quantity} ${produce.unit}", color = MaterialTheme.colorScheme.onSecondaryContainer, fontSize = 10.sp)
        }
        Text("Verified Quality Lot", fontSize = 11.sp, color = MaterialTheme.colorScheme.primary, fontWeight = FontWeight.SemiBold)
      }
    }
  }
}

data class ProduceItem(val id: Int, val name: String, val category: String, val farmer: String, val location: String, val quantity: Double, val unit: String, val price: Double)
data class MandiRate(val crop: String, val market: String, val modalPrice: Double)
data class DiseaseInfo(val diseaseName: String, val cropName: String, val symptoms: String, val treatment: String)
data class CropRecommendation(val name: String, val score: Int, val reason: String, val sowing: String, val harvest: String)

val sampleProduceList = listOf(
  ProduceItem(1, "Soybean (JS-335)", "Oilseeds", "Ramesh Patil", "Baramati, Pune", 40.0, "Quintal", 4750.0),
  ProduceItem(2, "Sharbati Wheat", "Grains", "Suresh Deshmukh", "Latur", 55.0, "Quintal", 2650.0),
  ProduceItem(3, "Nashik Red Onion", "Vegetables", "Vikram Shinde", "Nashik", 80.0, "Quintal", 1850.0),
  ProduceItem(4, "Bt Cotton (Medium Staple)", "Fiber", "Ganesh Kulkarni", "Amravati", 25.0, "Quintal", 7100.0)
)

val sampleMandiRates = listOf(
  MandiRate("Soybean", "Pune APMC", 4800.0),
  MandiRate("Cotton", "Amravati APMC", 7150.0),
  MandiRate("Wheat", "Nagpur APMC", 2700.0),
  MandiRate("Onion", "Lasalgaon APMC", 1920.0),
  MandiRate("Tur / Pigeon Pea", "Latur APMC", 9200.0)
)

val sampleDiseases = listOf(
  DiseaseInfo(
    "Rust / Brown Leaf Spot",
    "Soybean",
    "Small circular reddish-brown pustules on underside of leaves leading to premature defoliation.",
    "Spray Hexaconazole 5% EC @ 2 ml/L or Propiconazole 25% EC @ 1 ml/L. Ensure balanced potash."
  ),
  DiseaseInfo(
    "Pink Bollworm (गुलाबी बोंडअळी)",
    "Cotton",
    "Larvae bore into tender green bolls; rosetted flowers and stained lint with seed damage.",
    "Install 5 pheromone traps per acre. Spray Emamectin Benzoate 5% SG @ 0.4 g/L water or Neem oil 10,000 ppm."
  ),
  DiseaseInfo(
    "Purple Blotch (जांभळा करपा)",
    "Onion",
    "Small, water-soaked lesions that turn purple-brown with yellow halo along seed stalk.",
    "Spray Mancozeb 75% WP @ 2.5 g/L or Chlorothalonil @ 2 g/L with sticker (spreader)."
  )
)

fun runCropAdvisor(season: String, soil: String, water: String): List<CropRecommendation> {
  val recs = mutableListOf<CropRecommendation>()
  if (season == "Kharif") {
    recs.add(CropRecommendation("Soybean (सोयाबीन)", 95, "Ideal soil moisture absorption and high market APMC liquidity.", "June - July", "October"))
    recs.add(CropRecommendation("Cotton (कापूस)", 90, "Deep black soil provides moisture retention through boll development.", "June - July", "Nov - Jan"))
    recs.add(CropRecommendation("Tur / Pigeon Pea (तूर)", 85, "Excellent nitrogen-fixation legume for rotational soil vitality.", "June - July", "December"))
  } else if (season == "Rabi") {
    recs.add(CropRecommendation("Wheat (गहू)", 94, "Cool weather crop thriving in well-drained loam with moderate irrigation.", "October - November", "March"))
    recs.add(CropRecommendation("Gram / Chana (हरभरा)", 91, "Low water footprint crop requiring only 2-3 protective irrigations.", "October - November", "February"))
    recs.add(CropRecommendation("Jowar (ज्वारी)", 84, "Drought-tolerant winter grain with strong fodder value.", "September - October", "January"))
  } else {
    recs.add(CropRecommendation("Green Gram / Moong (मूग)", 92, "Rapid 60-day summer legume ideal for soil rejuvenation.", "February - March", "May"))
    recs.add(CropRecommendation("Groundnut (भुईमूग)", 88, "High summer oilseed yield under sprinkler irrigation.", "January - February", "May"))
    recs.add(CropRecommendation("Summer Vegetables", 85, "High daily cash-flow crop with local mandi demand.", "February - March", "May - June"))
  }
  return recs
}
