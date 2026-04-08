USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_ReadRotaPatternTeam]    Script Date: 15/11/2025 18:01:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 08-05-2022
-- Description:	Used to get rota and dutiy details bu team id
-- =============================================
CREATE OR ALTER        PROCEDURE [dbo].[usp_ReadRotaPatternTeam] 
	-- Add the parameters for the stored procedure here
	@teamId INT
AS
BEGIN
	SET NOCOUNT ON;

	select distinct SD.UD_DisplayName AS FullName, 
	       SPTL.SortCode, 
		   SD.UD_StaffNumber as StaffNumber, 
		   MR.RotaStartWeek, 
		   RP.RotaWeek AS iWeek, 
	       dayContainer.Saturday, 
		   dayContainer.Sunday, 
		   dayContainer.Monday, 
		   dayContainer.Tuesday, 
		   dayContainer.Wednesday, 
		   dayContainer.Thursday, 
		   dayContainer.Friday,
		   'Unknown' AS ContractType, 
		   SCP.UC_JobTitle AS TeamDescription,
		   HoursInRota, 
		   WeeksInRota, 
		   dayContainer.RotaWeek,
		   SCP.UC_EFT EFT,
		   MR.RotaID, 
		   RP.ScheduledPersonID, 
		   StartTime,
		   NightShiftCount,
		   DayShiftCount,
		   MiscDutyCount
	  from MasterRotas MR WITH (NOLOCK)
	 INNER JOIN (select * from
				 (select RD.rotaid,  
				         case when RD.DOTW = 0 then 'Saturday'
				              when RD.DOTW = 1 then 'Sunday' 
						      when RD.DOTW = 2 then 'Monday' 
							  when RD.DOTW = 3 then 'Tuesday'
							  when RD.DOTW = 4 then 'Wednesday' 
							  when RD.DOTW = 5 then 'Thursday' 
							  when RD.DOTW = 6 then 'Friday' END weekDayName, 
						DutyName,
						StartTime,
						md.masterdutyid,
						rd.RotaWeek,
						SUM(case when DutyTypeID = 1 
						     then CASE WHEN StartTime > EndTime 
							           THEN ROUND( CAST((((86400-md.starttime) + md.endtime )- ISNULL(md.BreakTime,0)) AS FLOAT) / 3600 ,2)
							           ELSE round((cast(((EndTime  - StartTime) - ISNULL(md.BreakTime,0)) as float) / 3600), 2) 
								   END
						     when (DutyTypeID > 1) AND (dutyname not in('-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*')) 
							 then round((cast(Duration as float) / 3600), 2) 
							 else 0
							 end ) OVER (partition by RD.rotaid, RD.MasterDutyID,rd.RotaWeek) as HoursInRota,
						SUM( case when( DutyTypeID = 1 AND ( StartTime >= 68400 OR StartTime < 10800)   AND (dutyname not in('-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*'))) THEN 1
							 when( DutyTypeID > 1 AND IsNightShift > 0  AND (dutyname not in('-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*'))) THEN 1
						         ELSE 0
							 end ) OVER (partition by RD.rotaid, RD.MasterDutyID,rd.RotaWeek) as NightShiftCount,  	
						SUM( case when( DutyTypeID = 1 AND ( StartTime < 68400 AND StartTime >= 10800)  AND (dutyname not in('-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*')) ) THEN 1
							when( DutyTypeID > 1 AND IsNightShift = 0  AND (dutyname not in('-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*'))) THEN 1
						         ELSE 0
							 end ) OVER (partition by RD.rotaid, RD.MasterDutyID,rd.RotaWeek) as DayShiftCount,
						SUM( case when( ISNULL(StartTime,0) = 0 AND ISNULL(EndTime,0) = 0 AND ISNULL(Duration,0) > 0  AND (dutyname not in('-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*')) ) THEN 1
						         ELSE 0
							 end ) OVER (partition by RD.rotaid, RD.MasterDutyID,rd.RotaWeek) as MiscDutyCount
															 					 						   
				  from  RotaDuties RD 
				  Inner Join MasterDuties MD ON RD.MasterDutyID = MD.MasterDutyID 
				  INNER JOIN MasterRotas MRI ON MRI.RotaID = RD.RotaID
				  WHERE RD.IsActive =1 
				    AND MD.IsActive = 1
					AND MRI.TeamID = @teamId 
					AND MRI.IsActive = 1
				 ) Vrota
				Pivot(
					max(DutyName) for weekDayName in(Saturday, Sunday, Monday, Tuesday, Wednesday, Thursday, Friday)
				    ) Ptbl
				) dayContainer ON dayContainer.RotaID = MR.RotaID
	Inner JOIN RotaPeople RP WITH (NOLOCK) ON RP.RotaID = MR.RotaID
	INNER JOIN ScheduledPersonTeam_LINK SPTL WITH (NOLOCK) ON RP.ScheduledPersonID = SPTL.ScheduledPersonID 
	       AND SPTL.TeamID = MR.TeamID
	LEFT JOIN UserDetails SD WITH (NOLOCK) ON SD.UD_UserID = SPTL.ScheduledPersonID
	LEFT JOIN UserConfigs SCP  WITH (NOLOCK) ON SCP.UC_UserID = SD.UD_UserID AND GETDATE() between SCP.UC_StartDate AND SCP.UC_EndDate
	WHERE MR.IsActive = 1 
		  AND RP.IsActive = 1 
		  AND GETDATE() between SPTL.StartDate and SPTL.EndDate
		  AND GETDATE() between rp.StartDate and rp.EndDate
		  AND sptl.scheduledType = 1
		  AND MR.TeamID = @teamId 
	  
	Order by SD.UD_DisplayName, dayContainer.RotaWeek, MiscDutyCount
END