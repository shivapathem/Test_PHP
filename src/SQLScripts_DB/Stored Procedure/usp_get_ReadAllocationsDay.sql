USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsDay]    Script Date: 30/03/2026 15:29:17 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                     PROCEDURE [dbo].[usp_get_ReadAllocationsDay]
@SchedulingTeamId     VARCHAR(100),
@roleIDPermission     VARCHAR(2),
@AllocationStartDate  DATE,
@AllocationEndDate    DATE,
@pNetLogin            VARCHAR(30) = NULL,
@pSortOrder            INT = NULL


AS

BEGIN

	SET NOCOUNT ON
	
    SET DATEFORMAT YMD


    DECLARE @vteamId              INT,
			@IsFreelancer         BIT = 0,
			@FreelancerSPID       INT ,
			@FLStartDate DATE, 
	        @FLEndDate DATE,
			@return_value int;

	DECLARE @TempLockStatus TABLE (
									WeekNumber			INT,
									iDay				INT,
									SchedulingTeamId	INT,
									IsManualLock		INT,
									IsEditable			INT,
									LockStatus			INT
									);
								   

	IF ( @pSortOrder IS NULL)  SET @pSortOrder = 0 ;
	
							   
	
    SELECT @FreelancerSPID = MAX(SP.UD_UserID),
		   @FLStartDate = MIN(STL.startdate),
		   @FLEndDate = MAX(STL.enddate)
	  FROM UserDetails SP (NOLOCK)
	 INNER JOIN ScheduledPersonTeam_LINK AS STL (NOLOCK) ON SP.UD_UserID = STL.scheduledpersonid
	 INNER JOIN schedulingTeams ST (NOLOCK) ON ST.schedulingTeamId = STL.TeamID
	 WHERE ST.schedulingTeamName IN ('Freelancers','Other BBC')
	   AND SP.UD_NetLogin = @pNetLogin
	   AND STL.IsHomeTeam = 1
	   AND STL.scheduledType = 1
	   AND STL.startdate <= CONVERT(DATETIME,@AllocationEndDate,101) and STL.enddate >= CONVERT(DATETIME,@AllocationStartDate,101)
	   
	 IF ( ISNULL(@FreelancerSPID,0) > 0 )
	  BEGIN
	    SET @IsFreelancer = 1
	  END	
	 ELSE
	  BEGIN
		SET  @FLStartDate = CONVERT(DATETIME,@AllocationStartDate,101)
		SET  @FLEndDate = CONVERT(DATETIME,@AllocationEndDate,101)		 
	  END	
	  
			INSERT INTO @TempLockStatus
			SELECT WeekNumber,
					iDay,
					schedulingTeamId,
					CASE WHEN DutyDate = ReleasedDate THEN 0 
						WHEN DutyDate = LockedDate   THEN 1
						ELSE 2 END AS ManualLock,						   						   
					CASE WHEN @roleIDPermission = 0 THEN 1
						WHEN DutyDate = ISNULL(LockedDate,DATEADD(Day,1,DutyDate)) 
							AND @roleIDPermission <> 0 THEN 0
						WHEN DutyDate between ISNULL(ReleasedDate,EditingStartDate) AND ISNULL(ReleasedDate,EditingEndDate)
							AND DutyDate <> ISNULL(LockedDate,DATEADD(Day,1,DutyDate))
						    AND ISNULL(@roleIDPermission,2) in (1,3)
							AND DutyDate <= CAST(getdate()+isnull(dailyViewMaskingDays,999) AS DATE)
						THEN 1
						WHEN DutyDate between ISNULL(ReleasedDate,EditingStartDate) AND ISNULL(ReleasedDate,EditingEndDate)
							AND DutyDate <> ISNULL(LockedDate,DATEADD(Day,1,DutyDate))
						    AND ISNULL(@roleIDPermission,2)  in (1,3)
							AND DutyDate > CAST(getdate()+isnull(dailyViewMaskingDays,999) AS DATE)
						THEN 1 ELSE 0 END AS IsEditable,
					CASE WHEN DutyDate between ISNULL(ReleasedDate,EditingStartDate) AND ISNULL(ReleasedDate,EditingEndDate)
							AND DutyDate <> ISNULL(LockedDate,DATEADD(Day,1,DutyDate))
						THEN 1 ELSE 0 END AS LockStatus
				FROM
					(SELECT td.ixYearWeek WeekNumber,
							td.ixDayInWeek iDay,
							CASE WHEN RD.[status]=1 THEN CAST(RD.ddate AS DATE) END AS ReleasedDate,
							CASE WHEN RD.[status]=0 THEN CAST(RD.ddate AS DATE) END AS LockedDate,
							CASE WHEN DATENAME(WEEKDAY,GETDATE()) = 'Friday'
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60 < ST.editingEnd
									AND ISNULL(st.restrictedEditing,0) = 1
									AND ST.WeekendOnly = 0 AND ST.numberofDaysAllowedEditing > 0 
											THEN CAST(GETDATE()-1 AS DATE)
								WHEN DATENAME(WEEKDAY,GETDATE()) = 'Friday'
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60 > ST.editingStart
									AND ISNULL(st.restrictedEditing,0) = 1
									AND (ST.WeekendOnly = 1 OR ST.numberofDaysAllowedEditing > 0 )
									THEN CAST(GETDATE() AS DATE)
									WHEN DATENAME(WEEKDAY,GETDATE()) = 'Saturday'
									AND ISNULL(st.restrictedEditing,0) = 1
									AND (ST.WeekendOnly = 1 OR ST.numberofDaysAllowedEditing > 0 )
											THEN CAST(GETDATE()-1 AS DATE)
									WHEN DATENAME(WEEKDAY,GETDATE()) = 'Sunday'
									AND ISNULL(st.restrictedEditing,0) = 1
									AND (ST.WeekendOnly = 1 OR ST.numberofDaysAllowedEditing > 0 )
											THEN CAST(GETDATE()-2 AS DATE)
						            WHEN DATENAME(WEEKDAY,GETDATE()) = 'Monday'
									AND ISNULL(st.restrictedEditing,0) = 1
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60 < st.editingEnd
									AND (ST.WeekendOnly = 1 OR ST.numberofDaysAllowedEditing > 0 )
											THEN CAST(GETDATE()-3 AS DATE)
                                WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(st.restrictedEditing,0) = 1
									AND ISNULL(ST.WeekendOnly,0) = 0
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60  < ST.editingEnd
								THEN CAST(getdate()-1 AS DATE)
								WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(ST.autoLockTodayTimer,0)=1
									AND ISNULL(restrictedEditing,0) = 1 AND ISNULL(ST.WeekendOnly,0) = 0
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60 > ST.editingStart
								THEN CAST(GETDATE() AS DATE)	
								WHEN ST.numberofDaysAllowedEditing > 1 AND ISNULL(ST.autoLockTodayTimer,0)=1
									AND ISNULL(restrictedEditing,0) = 1 AND ISNULL(ST.WeekendOnly,0) = 0 
								THEN CAST(GETDATE()+1 AS DATE)
								WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(ST.autoLockTodayTimer,0)=0
									AND ISNULL(ST.WeekendOnly,0) = 0 AND ISNULL(st.restrictedEditing,0) = 1
								THEN CAST(GETDATE() AS DATE)																			
							ELSE NULL END AS EditingStartDate,
							CASE WHEN  ISNULL(st.restrictedEditing,0) = 1 AND ST.WeekendOnly = 1 
				                THEN CASE WHEN DATENAME(WEEKDAY,getdate()) = 'Friday'
											AND DATENAME(HH,getdate())*3600 +  DATENAME(HH,GETDATE())*60 > ST.editingStart
											THEN CAST(getdate()+3 AS DATE)
											WHEN DATENAME(WEEKDAY,getdate()) IN ('Saturday')
											THEN CAST(getdate()+2 AS DATE)
											WHEN DATENAME(WEEKDAY,getdate()) IN ('Sunday')
											THEN CAST(getdate()+1 AS DATE)
											WHEN DATENAME(WEEKDAY,getdate()) = 'Monday'
											AND DATENAME(HH,getdate())*3600 +  DATENAME(HH,GETDATE())*60 < st.editingEnd
											THEN CAST(GETDATE() AS DATE)
										ELSE NULL END
								WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(ST.WeekendOnly,0) = 0
									AND ISNULL(st.restrictedEditing,0) = 1
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60 > ST.editingStart
									AND DATENAME(WEEKDAY,getdate()) = 'Friday'
								THEN CAST(GETDATE()+ CASE WHEN st.numberofDaysAllowedEditing < 3 THEN 3	
									ELSE st.numberofDaysAllowedEditing END							
										                AS DATE)										  
								WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(ST.WeekendOnly,0) = 0
									AND ISNULL(st.restrictedEditing,0) = 1
									AND DATENAME(WEEKDAY,getdate()) in ('Saturday','Sunday')
								THEN CAST(GETDATE()+ 
										CASE WHEN DATENAME(WEEKDAY,getdate()) = 'Saturday' AND st.numberofDaysAllowedEditing < 2 THEN 2
											WHEN DATENAME(WEEKDAY,getdate()) = 'Sunday' AND st.numberofDaysAllowedEditing <= 1 THEN 1 	
											ELSE st.numberofDaysAllowedEditing END							
										    AS DATE) 
						        WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(ST.autoLockTodayTimer,0)=1
									AND ISNULL(ST.WeekendOnly,0) = 0 AND ISNULL(st.restrictedEditing,0) = 1
							        AND DATENAME(HH,getdate())*3600 +  DATENAME(HH,GETDATE())*60 < st.editingEnd
								THEN CAST(GETDATE()+st.numberofDaysAllowedEditing-1 AS DATE)
								WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(ST.autoLockTodayTimer,0)=1
									AND ISNULL(ST.WeekendOnly,0) = 0 AND ISNULL(st.restrictedEditing,0) = 1
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60 > ST.editingStart
								THEN CAST(GETDATE()+CASE WHEN st.numberofDaysAllowedEditing = 1 THEN 1 ELSE st.numberofDaysAllowedEditing-1 END AS DATE)
								WHEN ST.numberofDaysAllowedEditing > 0 AND ISNULL(ST.autoLockTodayTimer,0)=0
									AND ISNULL(ST.WeekendOnly,0) = 0 AND ISNULL(st.restrictedEditing,0) = 1
									AND DATENAME(HH,GETDATE())*3600 +  DATENAME(HH,GETDATE())*60 > ST.editingStart
								THEN CAST(GETDATE()+CASE WHEN st.numberofDaysAllowedEditing = 1 THEN 1 ELSE st.numberofDaysAllowedEditing-1 END AS DATE)
								WHEN ST.numberofDaysAllowedEditing > 0 
									AND ISNULL(ST.WeekendOnly,0) = 0  AND ISNULL(st.restrictedEditing,0) = 1
								THEN CAST(GETDATE()+st.numberofDaysAllowedEditing-1 AS DATE)
							ELSE NULL END AS EditingEndDate,
							CAST(dDateTime AS DATE)  AS DutyDate,
							st.schedulingTeamId,
							ST.dailyViewMaskingDays
						FROM TimeDimension TD (NOLOCK)
						INNER JOIN schedulingTeams ST (NOLOCK) ON 1=1
						LEFT JOIN released_days RD (NOLOCK) ON RD.dDate = TD.dDateTime AND RD.schedulingTeamId=ST.schedulingTeamId
						WHERE td.dDateTime between @AllocationStartDate AND @AllocationEndDate
						AND ST.schedulingTeamId IN ( select value FROM string_split(@SchedulingTeamId,',') )
						) ID 

  	
		
	 SELECT AL.* 
	  FROM 
		   (SELECT sp.UD_DisplayName    				AS DisplayName,
	               sp.UD_DisplayLastName               as DisplayLastName,
				   sp.UD_DisplayFirstName              as DisplayFirstName,
				   sp.UD_TeampayStaffID                AS StaffDetailsID,
				   sp.UD_TeampayStaffID                AS StaffID,
				   sp.UD_StaffNumber                   AS StaffNumber,
				   UD_UserID				            AS SchedulingPersonID,
				   AL.AL_WeekNumber                    AS WeekNumber,
				   sp.UD_DisplayName					 AS FullName,
				    Isnull(UD_InternalEmail, '')   AS StaffInternalEmail,
					Isnull(UD_ExternalEmail, '')   AS StaffExernalEmail,
				    ISNULL(Asp_SortCode,ISNULL(spl.sortcode,''))  AS SortCode,
					AL.AL_SchedulingTeamID            AS schedulingTeamId,
					ad.AD_DutyStartTimeLocal                   AS StartDate,
					ad.AD_DutyEndTimeLocal                     AS EndDate,
					CASE WHEN ASP_SigninStatus IS NULL THEN NULL
						 WHEN ISNULL(ASP_SigninStatus,0) IN ( 0)
						 THEN 0 
						 WHEN ISNULL(ASP_SigninStatus,0) IN ( 1,2 ) 
						 THEN 1 END AS active,
					ASP_SigninINBuilding AS inBuilding,
					ASP_SigninStartTime                   AS SignInStartTime,
					ASP_SigninEndTime                     AS SignInEndTime,
					AD_IsEditedDutyAttention              AS isEdited,  
					AJ_IsEditedJobAttention               AS isJobEdited,  
					1                  AS isEditable,
					AD_AllocationsDutyID                          AS DutyID,
					al.AL_AllocationsID                AS DutyParentID,
					ISNULL(AD_DutyProgramID1,0)     AS dutyProgramId,
					1                              AS iscopy,
				   CASE WHEN AD_DutyName IS NULL THEN 'U'
						WHEN AD_DutyType IN (8,9,11,12)
							 THEN CASE WHEN ASP_LeaveType = 1
									   THEN 'Leave'
									   WHEN ASP_LeaveType = 2
									   THEN 'OFF Leave'
									   WHEN ASP_LeaveType = 3
									   THEN 'Sick'
									   WHEN ASP_LeaveType = 4
									   THEN 'U-Sick'
									   WHEN ASP_LeaveType = 5
									   THEN '-Sick'
									   WHEN ASP_LeaveType = 7
									   THEN 'Absent'
								   END
						ELSE AD_DutyName
						 END		  			      AS DutyName,
					TD1.DayNumber                  AS DayNumber,
					AD_StartTimeSec                  AS StartTime,
					AD_EndTimeSec                     AS EndTime,
					CASE WHEN AD_DutyType IN (8,9,11,12)
						 THEN ASP_LeaveDuration
						 ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
					0                              AS InternalEdited,
					AD.AD_DutyColourID			AS dutyColorId,
					AD_Comments                AS DutyComments,
					ASP_Comments              AS PersonComments,
					AJ_AllocateJobID                         AS JobID,
					aj.AJ_AllocationsDutyID               AS JobParentID,
					sp.UD_UserID          AS JobSchedulingPersonID,
					aj.AJ_JobStartTimeSec                   AS JobStartTime,
					aj.AJ_JobEndTimeSec                     AS JobEndTime,
					ISNULL(aj.AJ_JobName,'')          AS JobName,
		            pg.Programme					 AS Programme,		
					ISNULL(aj.AJ_ProgrammeID ,0)      AS ProgrammeId,								
					aj.AJ_JobBGColour               AS JobBackColour,
					aj.AJ_JobFontColour               AS JobFontColour,
					aj.AJ_Comments                    AS JobComments,
					aj.AJ_JobInfo                    AS Job_Info,
					AD_isAttention                 AS IsAttention,
					AD_isRequest                   AS IsRequest,
					1				                 AS isPublished,
					td.ixDayInWeek                        AS iDay,
					TD.dDateTime					     AS DutyDate,
					UD_UserID           AS ScheduledPersonID,
					scp.UC_ManualEDP                  AS ManualEDP,
					ISNULL(scp.UC_CostCode,'')      AS CostCode,
					ISNULL(ASP_MarkedOverTime,0)              AS MarkedOvertime,
					ISNULL(AD.ASP_OverTimeHours,0)              AS MannualOThours,
					ASP_DutyTeamID                  AS DutyTeamID,
					CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0
					      AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID THEN 0
						 WHEN ASP_LeaveStatus IN (3,4,5) THEN 0						
						ELSE 1 END          AS isDutyEditable,
				   spl.backgroundcolour     AS PersonBackgroundColour,
				   spl.fontcolour           AS PersonFontColour,
				   spl.ishometeam           AS IsHomeTeam,
				   1 AS IsDayEditable,
				   FD.IsManualLock            AS ManualLock,
				   FD.LockStatus            AS LockStatus,
				   case when cast(AJ_JobStartTimeLocal as date) > td.dDateTime
				             OR cast(AJ_JobEndTimeLocal as date) > td.dDateTime 
						then 1 else 0 end as MidnightFlag,
				   case when cast(AD_DutyStartTimeLocal as date) > td.dDateTime
							 OR cast(AD_DutyEndTimeLocal as date) > td.dDateTime
							 then 1 else 0 end as DutyMidnightFlag,
				   CASE WHEN AD_DutyName = 'U' AND AJ.AJ_JobStatus = 1 then 'UJ'
				        WHEN AD_DutyStatus = 0 THEN 'UD'
						WHEN AD_DutyStatus = 1 THEN 'AD'
						WHEN spl.ScheduledPersonID > 0 THEN 'AD'
						WHEN 1 = 2 THEN 'EU'
					END AS Grid,
					ColourBackground,ColourFont,
					ISNULL(la.LeaveStartTime,0) as LeaveStartTime,
					ISNULL(la.LeaveEndTime,0) as LeaveEndTime,
					case when ASP_ChargingStatus = 4 then 'Yellow'
						when ASP_ChargingStatus = 3 then 'Green' 
						when ASP_ChargingStatus = 2 then 'Red'
						when ASP_ChargingStatus = 1 then 'Blue'
						else 'None' end AS   TriangleColour,
					AD_DutyProgramID2		as  DutyProgramId2, 
					AD_DutyProgramID3		as  DutyProgramId3, 
					AD_DutyProgramID4		as  DutyProgramId4, 
					AD_DutyProgramID5		as DutyProgramId5, 
					AD_DutyProgramID6		as  DutyProgramId6,
					AD_AllocationsDutyID	as  AllocationsDutyID,
					ASP_AllocationsSPID		as AllocationsSPID,
					CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID = AL.AL_SchedulingTeamID THEN 1
					     WHEN ISNULL(ASP_DutyTeamID,0) = 0 AND spl.IsHomeTeam = 1 THEN 1
						 WHEN spl.IsHomeTeam IN (0,2) AND spl.IsAvailable = 1 AND ISNULL(AD_DutyType,7) IN (7,9) THEN 1
						 WHEN spl.IsHomeTeam IN (0,2) AND spl.IsAvailable = 0 AND AAP.AAP_AllocationsAPID IS NOT NULL
												 AND ISNULL(AD_DutyType,7) IN (7,9) THEN 1
						 ELSE 0 END AS AddSPExclFilter,
						 spl.DisplayInViewScreen AS DisplayInViewScreen
               FROM Allocations AS AL (nolock)
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
	         INNER JOIN Timedimension TD (nolock) on TD.ixYearWeek = AL_WeekNumber 
			 INNER JOIN UserDetails AS sp (nolock) ON UD_UserID = spl.ScheduledPersonID
			 LEFT JOIN 	(
							select AL_AllocationsID,
								   AL.AL_SchedulingTeamID,								   
								   AD_AllocationsDutyID,
									AD_DutyName,
									AD_Duration,
									AL_WeekNumber,
									AD_iDay,
									AD_StartTimeSec,
									AD_EndTimeSec,
									AD_DutyBreakTime,
									AD_DutyDate,
									AD_DutyStartTimeUTC,
									AD_DutyEndTimeUTC,
									AD_DutyStartTimeLocal,
									AD_DutyEndTimeLocal,
									AD_MasterDutyID,
									AD_DutyType,
									AD_DutyStatus,
									CASE WHEN AD_DutyType IN (8,9,11,12)
										THEN ASP_LeaveColourID
										ELSE AD.AD_DutyColourID END AS AD_DutyColourID,
									AD_Comments,
									AD_isAttention,
									AD_isRequest,
									AD_DutyProgramID1,
									AD_DutyProgramID2,
									AD_DutyProgramID3,
									AD_DutyProgramID4,
									AD_DutyProgramID5,
									AD_DutyProgramID6,
									AD_PlannedDuration,
									AD_PlannedDutyBreakTime,
									ASP_OverTwelveStatus,
									ASP_OverTwelveHrs,
									AD_IsNeedCovering,
									AD_IsOverrideOver12,
									ASP_AllocationsSPID,
									ASP_AllocationsDutyID,
									ASP_SchedulingPersonID,
									ASP_iDay,
									ASP_SortCode,
									ASP_LeaveStatus,
									ASP_LeaveType,
									ASP_DutyDate,
									ASP_WIADStatus,
									ASP_MarkedOverTime,
									ASP_OverTimeHours,
									ASP_DutyTeamID,
									ASP_SigninStartTime,
									ASP_SigninEndTime,
									ASP_SigninStatus,
									ASP_SigninINBuilding,
									ASP_EDPStatus,
									ASP_Comments,
									ASP_LeaveStartTimeSec,
									ASP_LeaveEndTimeSec,
									ASP_LeaveStartTimeLocal,
									ASP_LeaveEndTimeLocal,
									ASP_UnderElevenBreakStatus,
									ASP_CalculatedUnderElevenHrs,
									ASP_OverrideUnderElevenHrs,
									ASP_RequestsStatus,
									ASP_RequestsCount,
									ASP_LockRequestsStatus,
									ASP_ChargingStatus,
									0  AS AAP_AllocationsAPID,
									AD_IsEditedDutyAttention,
									ASP_LeaveDuration,
									ASP_LeaveColourID,
									1 AS IsShowDuty
							  FROM Allocations AS AL (nolock)
							 INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek							 
							 INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
																	   AND TD.ixDayInWeek = ASP_iDay
							 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
															AND AL_AllocationsID = AD_AllocationsID
							 WHERE dDateTime between @AllocationStartDate AND @AllocationEndDate
							   and AL_SchedulingTeamID IN ( select value FROM string_split(@SchedulingTeamId,',') )							   
							   AND AL_Status in (0,1)
							UNION All
							select  AL.AL_AllocationsID,
								    AL.AL_SchedulingTeamID,
									AD_AllocationsDutyID,
									AD_DutyName,
									AD_Duration,
									AL.AL_WeekNumber,
									AD_iDay,
									AD_StartTimeSec,
									AD_EndTimeSec,
									AD_DutyBreakTime,
									AD_DutyDate,
									AD_DutyStartTimeUTC,
									AD_DutyEndTimeUTC,
									AD_DutyStartTimeLocal,
									AD_DutyEndTimeLocal,
									AD_MasterDutyID,
									AD_DutyType,
									AD_DutyStatus,									
									CASE WHEN AD_DutyType IN (8,9,11,12)
										THEN ASP_LeaveColourID
										ELSE AD.AD_DutyColourID END AS AD_DutyColourID,
									AD_Comments,
									AD_isAttention,
									AD_isRequest,
									AD_DutyProgramID1,
									AD_DutyProgramID2,
									AD_DutyProgramID3,
									AD_DutyProgramID4,
									AD_DutyProgramID5,
									AD_DutyProgramID6,
									AD_PlannedDuration,
									AD_PlannedDutyBreakTime,
									ASP_OverTwelveStatus,
									ASP_OverTwelveHrs,
									AD_IsNeedCovering,
									AD_IsOverrideOver12,
									ASP_AllocationsSPID,
									ASP_AllocationsDutyID,
									ASP_SchedulingPersonID,
									ASP_iDay,
									AA.AAP_SortCode  ASP_SortCode,
									ASP_LeaveStatus,
									ASP_LeaveType,
									ASP_DutyDate,
									ASP_WIADStatus,
									ASP_MarkedOverTime,
									ASP_OverTimeHours,
									ASP_DutyTeamID,
									ASP_SigninStartTime,
									ASP_SigninEndTime,
									ASP_SigninStatus,
									ASP_SigninINBuilding,
									ASP_EDPStatus,
									AA.AAP_Comments ASP_Comments,
									ASP_LeaveStartTimeSec,
									ASP_LeaveEndTimeSec,
									ASP_LeaveStartTimeLocal,
									ASP_LeaveEndTimeLocal,
									ASP_UnderElevenBreakStatus,
									ASP_CalculatedUnderElevenHrs,
									ASP_OverrideUnderElevenHrs,
									ASP_RequestsStatus,
									ASP_RequestsCount,
									ASP_LockRequestsStatus,
									ASP_ChargingStatus,
									0  AS AAP_AllocationsAPID,
									AD_IsEditedDutyAttention,
									ASP_LeaveDuration,
									ASP_LeaveColourID,
									CASE WHEN AL_AllocationsID = AD_AllocationsID THEN 1
										 ELSE 0 END AS IsShowDuty
							  FROM Allocations AS AL (nolock)
							 INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek
							 INNER JOIN AllocationsAddPersons AA on AL_AllocationsID = AAP_AllocationsID 
																AND TD.ixDayInWeek = AA.AAP_iDay
							 INNER JOIN AllocationsScheduledPersons ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
							 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID															
							 WHERE dDateTime between @AllocationStartDate AND @AllocationEndDate
							   and AL.AL_SchedulingTeamID IN ( select value FROM string_split(@SchedulingTeamId,',') )
							   AND AL.AL_Status in (0,1)
						 ) AD on spl.TeamID = AD.AL_SchedulingTeamID 
							AND spl.ScheduledPersonID = AD.ASP_SchedulingPersonID
							and td.ixDayInWeek = AD.ASP_iDay
							and td.ixYearWeek = ad.AL_WeekNumber
		      INNER JOIN ( SELECT DENSE_RANK() over( order by td.dDateTime) as DayNumber,
		                          ixYearWeek WeekNumber,
							      ixDayInWeek iDay
		                     FROM Timedimension TD (NOLOCK)
					        WHERE TD.dDateTime between @AllocationStartDate and @AllocationEndDate ) TD1
					ON TD1.WeekNumber=TD.ixYearWeek AND TD1.iDay = TD.ixDayInWeek			  			  
			   INNER JOIN @TempLockStatus FD ON FD.WeekNumber = AL.AL_WeekNumber
							          AND FD.iDay=TD.ixDayInWeek
							          AND FD.SchedulingTeamId = AL.AL_SchedulingTeamID	
			  LEFT JOIN AllocationsAddPersons AAP on AL.AL_AllocationsID = AAP.AAP_AllocationsID
												  AND spl.ScheduledPersonID = AAP.AAP_SchedulingPersonID  
												  AND td.ixDayInWeek = AAP.AAP_iDay
		       LEFT JOIN schedulingTeams ST (nolock) on st.schedulingTeamId =   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 
			                                                              THEN ASP_DutyTeamID 
																		  ELSE spl.TeamID END										  
			   LEFT JOIN AllocationsJobs AJ (NOLOCK) ON (AJ.AJ_AllocationsDutyID = AD.AD_AllocationsDutyID) AND ( IsNULL(aj.AJ_JobStatus,1)=1 )							
			   LEFT JOIN UserConfigs scp (nolock) ON scp.UC_UserID = UD_UserID
							 AND td.dDateTime BETWEEN scp.UC_StartDate AND scp.UC_EndDate
	           LEFT JOIN Programmes PG (NOLOCK) ON PG.ID = AJ.AJ_ProgrammeID
			   LEFT JOIN REF_MasterDutyColours as RMC (NOLOCK) ON RMC.MasterDutyColourID = AD_DutyColourID
			   LEFT JOIN LeaveApplications LA on LA.ddate = TD.dDateTime AND LA.schedulingpersonid = sp.UD_UserID and la.deleted = 0
												AND ISNULL(LA.LeaveTypeID,0) NOT IN ( 3,4,5)
		      WHERE 1 = CASE WHEN ISNULL(@IsFreelancer,0) = 1 
				              AND UD_UserID <>  ISNULL(@FreelancerSPID,0)
				   		      AND TD.dDateTime < CAST(getdate() - 1 AS DATE) 
						      AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
						      THEN 2 
			                WHEN ISNULL(@IsFreelancer,0) = 1 
							  AND UD_UserID <> ISNULL(@FreelancerSPID,0)
						   AND TD.dDateTime > getdate()+ISNULL(st.freelancerMaskingDays,999) 
						      AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
							  THEN 2
							ELSE 1 END	
				AND 1 = CASE WHEN ISNULL(@roleIDPermission,0)  in (1,2)
  				              AND TD.dDateTime > getdate()+ ( case when isnull(st.dailyViewMaskingDays,0) = 0 then 999 else st.dailyViewMaskingDays end)
							 THEN 2 ELSE 1 END
				AND SPL.TeamId in ( select value FROM string_split(@SchedulingTeamId,',')  ) 	            
				AND spl.scheduledType = 1
				AND TD.dDateTime between spl.StartDate and spl.EndDate
				AND TD.dDateTime between @AllocationStartDate and @AllocationEndDate
				AND AL_Status in (0,1)
				AND NOT EXISTS ( SELECT 1
								  FROM AllocationsDelPersons ADP
								 WHERE ADP_AllocationsID = AL.AL_AllocationsID
								   AND spl.ScheduledPersonID = ADP.ADP_SchedulingPersonID)
				AND FD.IsEditable = 1		
               UNION ALL
			   SELECT NULL    					AS DisplayName,
	               NULL							as DisplayLastName,
				   NULL							as DisplayFirstName,
				   NULL							AS StaffDetailsID,
				   NULL							AS StaffID,
				   NULL							AS StaffNumber,
				   NULL				            AS SchedulingPersonID,
				   AL.AL_WeekNumber                    AS WeekNumber,
				   NULL							AS FullName,
				   ''  AS StaffInternalEmail,
					''   AS StaffExernalEmail,
					''   AS SortCode,
					AL.AL_SchedulingTeamID            AS schedulingTeamId,
					ad.AD_DutyStartTimeLocal                   AS StartDate,
					ad.AD_DutyEndTimeLocal                     AS EndDate,
					NULL AS active,
					NULL                   AS inBuilding,
					NULL                   AS SignInStartTime,
					NULL                     AS SignInEndTime,
					AD_IsEditedDutyAttention              AS isEdited,  
					AJ_IsEditedJobAttention               AS isJobEdited,  
					1                  AS isEditable,
					AD_AllocationsDutyID                          AS DutyID,
					al.AL_AllocationsID                AS DutyParentID,
					ISNULL(AD_DutyProgramID1,0)     AS dutyProgramId,
					1                              AS iscopy,
					ISNULL(AD_DutyName,'U')                    AS DutyName,
					TD1.DayNumber                  AS DayNumber,
					AD_StartTimeSec                   AS StartTime,
					AD_EndTimeSec                     AS EndTime,
					AD_Duration                    AS Duration,
					0                              AS InternalEdited,
					AD_DutyColourID                 AS dutyColorId,
					AD_Comments                AS DutyComments,
					NULL              AS PersonComments,
					AJ_AllocateJobID                         AS JobID,
					aj.AJ_AllocationsDutyID               AS JobParentID,
					0          AS JobSchedulingPersonID,
					aj.AJ_JobStartTimeSec                   AS JobStartTime,
					aj.AJ_JobEndTimeSec                     AS JobEndTime,
					ISNULL(aj.AJ_JobName,'')          AS JobName,
		            pg.Programme					 AS Programme,		
					ISNULL(aj.AJ_ProgrammeID ,0)      AS ProgrammeId,								
					aj.AJ_JobBGColour               AS JobBackColour,
					aj.AJ_JobFontColour               AS JobFontColour,
					aj.AJ_Comments                    AS JobComments,
					aj.AJ_JobInfo                    AS Job_Info,
					AD_isAttention                 AS IsAttention,
					AD_isRequest                   AS IsRequest,
					1				                 AS isPublished,
					td.ixDayInWeek                        AS iDay,
					TD.dDateTime					     AS DutyDate,
					0           AS ScheduledPersonID,
					0                  AS ManualEDP,
					''      AS CostCode,
					0              AS MarkedOvertime,
					0              AS MannualOThours,
					0                  AS DutyTeamID,
					1           AS isDutyEditable,
				   NULL     AS PersonBackgroundColour,
				   NULL           AS PersonFontColour,
				   NULL           AS IsHomeTeam,
				   1                        AS IsDayEditable,
				   FD.IsManualLock            AS ManualLock,
				   FD.LockStatus            AS LockStatus,
				   case when cast(AJ_JobStartTimeLocal as date) > td.dDateTime
				             OR cast(AJ_JobEndTimeLocal as date) > td.dDateTime 
						then 1 else 0 end as MidnightFlag,
				   case when cast(AD_DutyStartTimeLocal as date) > td.dDateTime
							 OR cast(AD_DutyEndTimeLocal as date) > td.dDateTime
							 then 1 else 0 end as DutyMidnightFlag,
				   CASE WHEN AD_DutyName = 'Unassigned Job' AND IsNULL(aj.AJ_JobStatus,1) IN (0, 1) then 'UJ'
				        WHEN AD_DutyStatus = 0 THEN 'UD'
						WHEN AD_DutyStatus = 1 THEN 'AD'
						WHEN 1 = 2 THEN 'EU'
					END AS Grid,
					ColourBackground, ColourFont,
					0 as LeaveStartTime,
					0 as LeaveEndTime,
					'None'  AS   TriangleColour,
					AD_DutyProgramID2		as  DutyProgramId2, 
					AD_DutyProgramID3		as  DutyProgramId3, 
					AD_DutyProgramID4		as  DutyProgramId4, 
					AD_DutyProgramID5		as DutyProgramId5, 
					AD_DutyProgramID6		as  DutyProgramId6,
					AD_AllocationsDutyID	as  AllocationsDutyID,
					0						as AllocationsSPID,
					1 AS AddSPExclFilter,
					1 AS DisplayInViewScreen
				FROM Allocations AL  
			   INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID  
			   INNER JOIN TimeDimension TD on AL_WeekNumber = TD.ixYearWeek AND TD.ixDayInWeek = AD.AD_iDay        
			   INNER JOIN schedulingTeams st on st.schedulingTeamId = al.AL_SchedulingTeamID  
		      INNER JOIN ( SELECT DENSE_RANK() over( order by td.dDateTime) as DayNumber,
		                          ixYearWeek WeekNumber,
							      ixDayInWeek iDay
		                     FROM Timedimension TD (NOLOCK)
					        WHERE TD.dDateTime between @AllocationStartDate and @AllocationEndDate ) TD1
					ON TD1.WeekNumber=TD.ixYearWeek AND TD1.iDay = TD.ixDayInWeek	
			   INNER JOIN @TempLockStatus FD ON FD.WeekNumber = AL.AL_WeekNumber
							          AND FD.iDay=TD.ixDayInWeek
							          AND FD.SchedulingTeamId = AL.AL_SchedulingTeamID	
			   LEFT JOIN AllocationsJobs AJ (NOLOCK) ON (AJ.AJ_AllocationsDutyID = AD.AD_AllocationsDutyID) AND ( IsNULL(aj.AJ_JobStatus,1) IN (0, 1) )
			   LEFT JOIN Programmes PG (NOLOCK) ON PG.ID = AJ.AJ_ProgrammeID
			   LEFT JOIN REF_MasterDutyColours as RMC (NOLOCK) ON RMC.MasterDutyColourID = AD.AD_DutyColourID
			   WHERE dDateTime between @AllocationStartDate AND @AllocationEndDate
				 AND AL_SchedulingTeamID in ( select value FROM string_split(@SchedulingTeamId,',')  )  
				 AND AD_DutyStatus = 0  
				  AND AL_Status in (0,1)
				 AND FD.IsEditable = 1	
               UNION ALL
			SELECT UD_DisplayName                  AS DisplayName,
			       UD_DisplayLastName              as DisplayLastName,
				   UD_DisplayFirstName             as DisplayFirstName,
				   0								AS StaffDetailsID,
				   0			                      AS StaffID,
				   UD_StaffNumber                  AS StaffNumber,
				   AL.schedulingpersonid           AS SchedulingPersonID,
				   AL.weeknumber                   AS WeekNumber,
				    UD_DisplayName					AS FullName,
				    Isnull(UD_InternalEmail, '') AS StaffInternalEmail,
					Isnull(UD_ExternalEmail, '') AS StaffExernalEmail,
					ISNULL(AL.SortCode,ISNULL(spl.sortcode,''))  AS SortCode,					
					AL.schedulingteamid            AS schedulingTeamId,
					AL.startdate                   AS StartDate,
					AL.enddate                     AS EndDate, 
	                SigninStatus                      AS active,
					SigninINBuilding                  AS inBuilding,
					SigninStartTime                   AS SignInStartTime,
					SigninEndTime                     AS SignInEndTime,
					AL.isedited                    AS isEdited,
					aj.isedited                    AS isJobEdited,
					AL.iseditable                  AS isEditable,
					AL.id                          AS DutyID,
					AL.allocationid                AS DutyParentID,
					ISNULL(AL.dutyProgramId,0)     AS dutyProgramId,
					1                              AS iscopy,
					AL.dutyname                    AS DutyName,
					TD1.DayNumber                  AS DayNumber,
					AL.starttime                   AS StartTime,
					AL.endtime                     AS EndTime,
					AL.duration                    AS Duration,
					0                              AS InternalEdited,
					AL.dutycolorid                 AS dutyColorId,									
					AL.dutycomments                AS DutyComments,
					AL.personcomments              AS PersonComments,
					aj.id                          AS JobID,
					aj.allocatejobid               AS JobParentID,
					aj.schedulingpersonid          AS JobSchedulingPersonID,
					aj.starttime                   AS JobStartTime,
					aj.endtime                     AS JobEndTime,
					ISNULL(aj.jobname,'')        AS JobName,
		            ISNULL(aj.Programme,pg.Programme) AS Programme, 
					ISNULL(aj.ProgrammeId,0)       AS ProgrammeId, 
		            aj.jobbackcolour               AS JobBackColour,
					aj.jobfontcolour               AS JobFontColour,
					aj.comments                    AS JobComments,
					aj.job_Info                    AS Job_Info,
					AL.isattention                 AS IsAttention,
					AL.isrequest                   AS IsRequest,
					AL.ispublished                 AS isPublished,
					AL.iday                        AS iDay,
					CONVERT(DATE, AL.dutydate)     AS DutyDate,
					sp.UD_UserID					AS ScheduledPersonID,
					scp.UC_ManualEDP                 AS ManualEDP,
					ISNULL(scp.UC_CostCode,'')      AS CostCode,
					AL.markedovertime              AS MarkedOvertime,
					AL.mannualothours              AS MannualOThours,
					AL.DutyTeamID                  AS DutyTeamID,					
					0                              AS isDutyEditable,
				    spl.backgroundcolour           AS PersonBackgroundColour,
				    spl.fontcolour                 AS PersonFontColour,
					spl.ishometeam                 AS IsHomeTeam,
					0                              AS IsDayEditable,
				    FD.IsManualLock                  AS ManualLock,
					FD.LockStatus                  AS LockStatus,
					ISNULL(aj.aftermidnight,0) as MidnightFlag,
				    ISNULL(AL.aftermidnight,0) as DutyMidnightFlag,
		            CASE WHEN AL.DutyName='U' AND AJ.ID IS NOT NULL AND ISNULL(AJ.isActive,1) = 1 then 'UJ'
				        WHEN ISNULL(AL.schedulingpersonid,0) = 0 AND ISNULL(AL.isActive,1)=1 THEN 'UD'
						WHEN ISNULL(AL.schedulingpersonid,0) > 0 THEN 'AD'
						WHEN ISNULL(AL.schedulingpersonid,0) > 0 AND spl.scheduledpersonid IS NULL THEN 'EU'
					END AS Grid,
					ColourBackground,ColourFont,
					ISNULL(AL.LeaveStartTime,0) as LeaveStartTime,
					ISNULL(AL.LeaveEndTime,0) as LeaveEndTime,
					null AS TriangleColour,
					al.DutyProgramId2, al.DutyProgramId3, al.DutyProgramId4, al.DutyProgramId5, al.DutyProgramId6,
					ISNULL(AllocationsDutyID,0)	as  AllocationsDutyID,
					ISNULL(AllocationsSPID,0)		as AllocationsSPID,
					1 AS AddSPExclFilter,
					spl.DisplayInViewScreen AS DisplayInViewScreen
               FROM Allocations_Publish AL (NOLOCK)
		      INNER JOIN Timedimension TD (NOLOCK) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek
		      INNER JOIN ( SELECT DENSE_RANK() over( order by td.dDateTime) as DayNumber,
		                    ixYearWeek WeekNumber,
							ixDayInWeek iDay
		              FROM Timedimension TD (NOLOCK)
					 WHERE TD.dDateTime between @AllocationStartDate and @AllocationEndDate ) TD1
		                 ON TD1.WeekNumber=TD.ixYearWeek AND TD1.iDay = TD.ixDayInWeek 
		      INNER JOIN schedulingTeams AS ST (NOLOCK)  ON AL.SchedulingTeamId = ST.SchedulingTeamId 
	          INNER JOIN @TempLockStatus FD ON FD.WeekNumber = AL.WeekNumber
							          AND FD.iDay=AL.iDay
							          AND FD.SchedulingTeamId = AL.SchedulingTeamId	
	           LEFT JOIN UserDetails AS sp (nolock) ON sp.UD_UserID = AL.schedulingpersonid		  			  
			   LEFT JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON AL.schedulingpersonid = spl.scheduledpersonid
						   AND spl.teamid=AL.SchedulingTeamId	
						   AND SPL.scheduledType = 1	
					       AND TD.dDateTime >= isnull(spl.startdate,TD.dDateTime)
			               AND TD.dDateTime <= isnull(spl.enddate,TD.dDateTime)									  																	
			   LEFT JOIN Allocations_Jobs_Publish aj (NOLOCK) ON (AL.ID = aj.AllocationID) AND ( IsNULL(aj.isActive,1)=1 )							
			   LEFT JOIN UserConfigs scp (nolock) ON UD_UserID = scp.UC_UserID
							 AND AL.dutydate BETWEEN ISNULL(scp.UC_StartDate,AL.dutydate)
							                     AND ISNULL(scp.UC_EndDate,AL.dutydate)												  	
			   LEFT JOIN Programmes PG (NOLOCK) ON PG.ID = AJ.ProgrammeId 
			   LEFT JOIN REF_MasterDutyColours as RMC (NOLOCK) ON RMC.MasterDutyColourID = AL.dutyColorId
		      WHERE AL.SchedulingTeamId = CASE WHEN ISNULL(AL.DutyTeamID,0) = 0 THEN AL.SchedulingTeamId ELSE AL.DutyTeamID END
				AND 1 = CASE WHEN ISNULL(@IsFreelancer,0) = 1 
				              AND AL.schedulingpersonid <> ISNULL(@FreelancerSPID,0)
				   		      AND TD.dDateTime < CAST(getdate() - 1 AS DATE) 
						      AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
							  THEN 2
							 WHEN ISNULL(@IsFreelancer,0) = 1 
							  AND AL.schedulingpersonid <> ISNULL(@FreelancerSPID,0)
							  AND TD.dDateTime > getdate()+ISNULL(st.freelancerMaskingDays,999) 
						      AND td.dDateTime BETWEEN @FLStartDate and @FLEndDate
							  THEN 2
							ELSE 1 END
				AND 1 = CASE WHEN ISNULL(@roleIDPermission,0)  in (1,2)
  				              AND TD.dDateTime > getdate() + ( case when isnull(st.dailyViewMaskingDays,0) = 0 then 999 else st.dailyViewMaskingDays end)
							 THEN 2 ELSE 1 END				
				AND AL.SchedulingTeamId in ( select value FROM string_split(@SchedulingTeamId,',') )
				AND FD.IsEditable = 0					
				AND TD.dDateTime between @AllocationStartDate and @AllocationEndDate ) AL 
			 WHERE AddSPExclFilter = 1
				ORDER BY CASE WHEN @pSortOrder = 0  then StartTime End,
				   CASE WHEN @pSortOrder = 0 THEN DisplayFirstName end, 
				   CASE WHEN @pSortOrder = 0 THEN DisplayLastName end, 
					case WHEN @pSortOrder = 1 THEN DutyName end,
				   CASE WHEN @pSortOrder = 1 THEN DisplayFirstName end, 
				   CASE WHEN @pSortOrder = 1 THEN DisplayLastName end, 
					case WHEN @pSortOrder = 2 THEN SortCode end,
				   CASE WHEN @pSortOrder = 2 THEN DisplayFirstName end, 
				   CASE WHEN @pSortOrder = 2 THEN DisplayLastName end


END