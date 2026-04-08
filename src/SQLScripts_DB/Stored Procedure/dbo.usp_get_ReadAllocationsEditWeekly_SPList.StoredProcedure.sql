USE [Allocate7]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER         PROCEDURE  [dbo].[usp_get_ReadAllocationsEditWeekly_SPList]
@startDate                 VARCHAR(22),
@EndDate                   VARCHAR(22),
@pteamId			       INT,
@filterCond                VARCHAR(MAX) = NULL,
@filterOrderCond           VARCHAR(MAX) = NULL
AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

    DECLARE @FilterSQL                 VARCHAR(MAX)
	DECLARE @VFilter                   VARCHAR(MAX)
	DECLARE @VLJFilter                 VARCHAR(MAX)	
	DECLARE @FilterSetFlag             INT = 0
	DECLARE @vleftjoinflag             INT = 0	

	SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1	 	 
	 
	
		     SELECT AL.ID, 
			       AL.SchedulingPersonID,					
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( ISNULL( sd.preferredforename,'') = '' ) THEN (
						   sd.forename + ' ' + sd.surname )
						   ELSE ( sd.preferredforename + ' ' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName,
				   AL.DutyName                                 As DutyName,
				   AL.dutyProgramId                            AS dutyProgramId,
				   ISNULL(AL.SortCode,spl.sortcode)            AS sortcode,
				   sp.DisplayLastName                          AS DisplayLastName,
				   sp.DisplayFirstName                         AS DisplayFirstName,
				   al.ishometeam                               AS IsHomeTeam,
				   sd.staffnumber                              AS StaffNumber,				   
				   spl.backgroundcolour                        AS PersonBackgroundColour,
				   spl.fontcolour                              AS PersonFontColour,		
				   sct.costcode                                AS CostCode,
				   sd.staffid                                  AS StaffID,				   
				   DENSE_RANK() OVER ( partition by schedulingpersonid ORDER BY  weeknumber, iday ) AS SPRank
		      INTO #TempGetAllocations				   
			  FROM Allocations AS AL (nolock)
	         INNER JOIN Timedimension TD (nolock) on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek				 
			 INNER JOIN ScheduledPeople AS sp (nolock) ON AL.schedulingpersonid  = sp.scheduledpersonid
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON AL.SchedulingPersonID = spl.scheduledpersonid  
							  AND AL.SchedulingTeamId = spl.teamid
							  AND CONVERT(DATETIME,@startDate,101) <=  isnull( spl.enddate, CONVERT(DATETIME,@startDate,101) ) 
							  AND CONVERT(DATETIME,@EndDate,101)  >=  isnull( spl.startdate, CONVERT(DATETIME,@EndDate,101))				  
			 LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid  	
			 LEFT JOIN StaffContract sct (nolock) ON sd.staffid = sct.staffid
							 AND AL.dutydate BETWEEN ISNULL(sct.startdate,AL.dutydate)
							                AND ISNULL( sct.enddate,AL.dutydate)
											AND ISNULL( SCT.isactive,1) = 1  			 
		     WHERE TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
			   AND AL.SchedulingTeamId = @pteamId
			   AND SPL.scheduledType =1
			   
		IF ( ISNULL(@filterOrderCond,'') <> '')
		 BEGIN
			SET @filterOrderCond = replace( @filterOrderCond, 'sp.DisplayName', 'DisplayName')
			SET @filterOrderCond = replace( @filterOrderCond, 'spl.SortCode', 'sortcode')
			SET @filterOrderCond = replace( @filterOrderCond, 'a.DutyName', 'DutyName')
			SET @filterOrderCond = replace( @filterOrderCond, 'sct.CostCode','CostCode') 		
			SET @filterOrderCond = replace( @filterOrderCond, 'aj.JobName', 'JobName')
			SET @filterOrderCond = replace( @filterOrderCond, 'aj.ProgrammeId','ProgrammeId') 	
			SET @filterOrderCond = replace( @filterOrderCond, 'spsl.programmes_id','programmes_id') 	
			SET @filterOrderCond = replace( @filterOrderCond, 'a.StartTime','StartTime')
			SET @filterOrderCond = replace( @filterOrderCond, 'a.dutyProgramId','dutyProgramId')				
         END		
				
		IF ( ISNULL(@filterCond,'') = '')
		  BEGIN		  
			   SET @FilterSQL = 'SELECT DISTINCT SchedulingPersonID,DisplayName, SortCode, DisplayLastName,  DisplayFirstName,
			                            IsHomeTeam, StaffNumber, PersonBackgroundColour,PersonFontColour
			                       FROM #TempGetAllocations  WHERE SPRank=1'	
							
				 SET @FilterSQL = @FilterSQL + ISNULL(@filterOrderCond,' ')
		  END
		ELSE
		 BEGIN
		   		  
			  SET @VFilter = replace( @filterCond, 'sp.DisplayName', 'DisplayName')
			  SET @VFilter = replace( @VFilter, 'spl.SortCode', 'sortcode')
			  SET @VFilter = replace( @VFilter, 'a.DutyName', 'DutyName')
			  SET @VFilter = replace( @VFilter, 'sct.CostCode','CostCode') 
			  SET @VFilter = replace( @VFilter, 'a.StartTime','StartTime')
			  SET @VFilter = replace( @VFilter, 'a.dutyProgramId','dutyProgramId')	
			  		  
			  IF ( CHARINDEX ('aj.JobName',@VFilter) > 0 OR CHARINDEX ('aj.ProgrammeId',@VFilter) > 0 )
			   BEGIN
			     
				 SET @vleftjoinflag = 1
				 
                  select AJ.AllocationID, AJ.JobName, AJ.ProgrammeID
				         INTO #TempJobs
				  from Allocations_Jobs AJ (nolock)
				  INNER JOIN #TempGetAllocations AL ON AL.ID= AJ.AllocationID
				  
                  SET @VLJFilter = ' INNER JOIN #TempJobs ON #TempGetAllocations.ID=#TempJobs.AllocationID '
                                     									 
			  
			   END	
			   
			  SET @VFilter = replace( @VFilter, 'aj.JobName', 'JobName')
			  SET @VFilter = replace( @VFilter, 'aj.ProgrammeId','ProgrammeId') 	

			  IF ( CHARINDEX ('spsl.programmes_id',@VFilter) > 0 )
			   BEGIN
			  
			    SET @vleftjoinflag = 1
			  
				  SELECT AL.ID, programmes_id
				         INTO #TempStaffSkills
				    FROM skills_programmes_staff_link spsl (nolock)
					INNER JOIN #TempGetAllocations AL ON spsl.staff_id = AL.StaffID 
					
                  SET @VLJFilter = ISNULL(@VLJFilter,'') + ' INNER JOIN #TempStaffSkills  ON #TempGetAllocations.ID=#TempStaffSkills.ID '
									 
			   END				  
		  
			  SET @VFilter = replace( @VFilter, 'spsl.programmes_id','programmes_id') 	
			  
			  IF (@vleftjoinflag = 1)
			    BEGIN
																			
				  SET @FilterSQL =  ' SELECT DISTINCT SchedulingPersonID,DisplayName, SortCode, DisplayLastName,  DisplayFirstName,
			                                 IsHomeTeam, StaffNumber, PersonBackgroundColour,PersonFontColour 
									  FROM #TempGetAllocations WHERE SchedulingPersonID IN ( 
				                           SELECT TG.SchedulingPersonID FROM #TempGetAllocations TG '
								    +@VLJFilter+' '+@VFilter+' ) '+@filterOrderCond		 								 
				
				END
			  ELSE
			    BEGIN
				  										
				   
				  SET @FilterSQL =  ' SELECT DISTINCT SchedulingPersonID,DisplayName, SortCode, DisplayLastName,  DisplayFirstName,
			                                 IsHomeTeam, StaffNumber, PersonBackgroundColour,PersonFontColour 
									 FROM #TempGetAllocations WHERE schedulingpersonid IN
									 ( SELECT schedulingpersonid FROM #TempGetAllocations WHERE 1=1 '+ @VFilter+' ) '+@filterOrderCond
				
				END
			  
		  END	  

         EXEC ( @FilterSQL )			   

		
END
			  