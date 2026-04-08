USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_CarryOverData]    Script Date: 27/07/2025 20:24:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_get_CarryOverData]
	-- Add the parameters for the stored procedure here
	@intTeamID int,
	@currentleaveyear int

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @leaveyearstartdate varchar(25)
	DECLARE @leaveyearenddate varchar(25)

	--SET @currentleaveyear = YEAR(getdate()) - 1

	set @leaveyearstartdate = DATEFROMPARTS(@currentleaveyear-1,04,01)
    set @leaveyearenddate = DATEFROMPARTS(@currentleaveyear,03,31) 

 			        SELECT UD_UserID,  
						   StaffNumber, AnnualRemaining as Annual_nextyear,
						   PHLRemaining as PHL_nextyear,
						   CompRemaining as Comp_nextyear,
						   AdditionalRemaining as Additional_nextyear,
						   ExceptionalRemaining as Exceptional_nextyear,
						   Under11TOILRemaining as Under11TOIL_nextyear,
						   Over12TOILRemaining as Over12TOIL_nextyear,
						   CasualRemaining as Casual_nextyear,
						   LongServiceRemaining as LongService_nextyear, 
						   OtherRemaining as Other_nextyear,
						   CASE WHEN AnnualRemaining < 0 THEN abs(AnnualRemaining) ELSE (AnnualRemaining * -1) END AS Annual_prevyear,
						   CASE WHEN PHLRemaining < 0 THEN abs(PHLRemaining) ELSE (PHLRemaining * -1) END AS PHL_prevyear,
						   CASE WHEN CompRemaining < 0 THEN abs(CompRemaining) ELSE (CompRemaining * -1) END AS Comp_prevyear,
						   CASE WHEN AdditionalRemaining < 0 THEN abs(AdditionalRemaining) ELSE (AdditionalRemaining * -1) END AS Additional_prevyear,
						   CASE WHEN ExceptionalRemaining < 0 THEN abs(ExceptionalRemaining) ELSE (ExceptionalRemaining * -1) END AS Exceptional_prevyear,
						   CASE WHEN Under11TOILRemaining < 0 THEN abs(Under11TOILRemaining) ELSE (Under11TOILRemaining * -1) END AS Under11TOIL_prevyear,
						   CASE WHEN Over12TOILRemaining < 0 THEN abs(Over12TOILRemaining) ELSE (Over12TOILRemaining * -1) END AS Over12TOIL_prevyear,
						   CASE WHEN CasualRemaining < 0 THEN abs(CasualRemaining) ELSE (CasualRemaining * -1) END AS Casual_prevyear,
						   CASE WHEN LongServiceRemaining < 0 THEN abs(LongServiceRemaining) ELSE (LongServiceRemaining * -1) END AS LongService_prevyear,
						   CASE WHEN OtherRemaining < 0 THEN abs(OtherRemaining) ELSE (OtherRemaining * -1) END AS Other_prevyear						    						  
				      FROM(
					  SELECT	(ISNULL(SUM(LA.Annual),0))- ISNULL(SUM(LT.AnnualTaken),0) AS AnnualRemaining,
						   (ISNULL(SUM(LA.Comp),0)) - ISNULL(SUM(LT.CompTaken),0) AS CompRemaining,
						   (ISNULL(SUM(LA.PHL),0)) - ISNULL(SUM(LT.PHLTaken),0) AS PHLRemaining,
						   (ISNULL(SUM(LA.Additional),0)) - ISNULL(SUM(LT.AdditionalTaken),0) AS AdditionalRemaining,
						   0 AS ExceptionalRemaining,
						   (ISNULL(SUM(LA.Under11TOIL),0)) - ISNULL(SUM(LT.Under11TOILTaken),0) AS Under11TOILRemaining,
						   (ISNULL(SUM(LA.Over12TOIL),0)) - ISNULL(SUM(LT.Over12TOILTaken),0) AS Over12TOILRemaining,
						   (ISNULL(SUM(LA.Casual),0)) - ISNULL(SUM(LT.CasualTaken),0) AS CasualRemaining,
						   (ISNULL(SUM(LA.LongService),0)) - ISNULL(SUM(LT.LongServiceTaken),0) AS LongServiceRemaining,
						   (ISNULL(SUM(LA.Other),0)) - ISNULL(SUM(LT.OtherTaken),0) AS OtherRemaining,
						   LA.UD_UserID,  
						   LA.StaffNumber	
					FROM ( SELECT	UD_UserID,	UD_StaffNumber StaffNumber, (ISNULL(SUM(LA.Annual),0))  Annual,
						   (ISNULL(SUM(LA.Comp),0))   AS Comp,
						   (ISNULL(SUM(LA.PHL),0))  AS PHL,
						   (ISNULL(SUM(LA.Additional),0))  AS Additional,
						   (ISNULL(SUM(LA.Exceptional),0))   AS Exceptional,
						   (ISNULL(SUM(LA.Under11TOIL),0))   AS Under11TOIL,
						   (ISNULL(SUM(LA.Over12TOIL),0))   AS Over12TOIL,
						   (ISNULL(SUM(LA.Casual),0))   AS Casual,
						   (ISNULL(SUM(LA.LongService),0))   AS LongService,
						   (ISNULL(SUM(LA.Other),0))   AS Other				   						  
					FROM Leaveallocation as LA (nolock)
					INNER JOIN UserDetails (nolock) ON UD_UserID = LA.SchedulingPersonID
					INNER JOIN (select sstl.ScheduledPersonID from Scheduledpersonteam_link sstl 
						        WHERE  sstl.TeamID IN (@intTeamID)
					              AND sstl.IsHomeTeam = 1
					              and ISNULL(convert(datetime,sstl.EndDate,110),'9999-01-01') >= cast (getdate() AS DATE)
					              and  convert(datetime,sstl.StartDate,110) <= cast (getdate()  AS DATE) ) AS stl  
					              ON stl.ScheduledPersonID = UD_UserID
										WHERE    LA.IsActive = 1 
					  AND LA.ddate >= CONVERT(DATETIME, @leaveyearstartdate , 102) 
					  AND  LA.ddate <= CONVERT(DATETIME, @leaveyearenddate , 102)
				     GROUP BY  UD_UserID, UD_StaffNumber
					) LA
					LEFT JOIN (  SELECT SchedulingPersonID, 
					         ISNULL(SUM(CASE WHEN LT.AllocName='Annual' THEN RA.Amount ELSE 0 END),0) AS AnnualTaken,
						    ISNULL(SUM(CASE WHEN LT.AllocName='Comp' THEN RA.Amount ELSE 0 END),0) AS CompTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='PHL' THEN RA.Amount ELSE 0 END),0) AS PHLTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='Additional' THEN RA.Amount ELSE 0 END),0) AS AdditionalTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='Exceptional' THEN RA.Amount ELSE 0 END),0) AS ExceptionalTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='Under11TOIL' THEN RA.Amount ELSE 0 END),0) AS Under11TOILTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='Over12TOIL' THEN RA.Amount ELSE 0 END),0) AS Over12TOILTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='Casual' THEN RA.Amount ELSE 0 END),0) AS CasualTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='LongService' THEN RA.Amount ELSE 0 END),0) AS LongServiceTaken,
						   ISNULL(SUM(CASE WHEN LT.AllocName='Other' THEN RA.Amount ELSE 0 END),0) AS OtherTaken
					 FROM LeaveApplications AS LAP (nolock) 	
					INNER JOIN (select sstl.ScheduledPersonID from Scheduledpersonteam_link sstl 
						        WHERE  sstl.TeamID IN (@intTeamID)
					              AND sstl.IsHomeTeam = 1
					              and ISNULL(convert(datetime,sstl.EndDate,110),'9999-01-01') >= cast (getdate() AS DATE)
					              and  convert(datetime,sstl.StartDate,110) <= cast (getdate()  AS DATE) ) AS stl  
					              ON stl.ScheduledPersonID = LAP.SchedulingPersonID					 				     
			                    INNER JOIN ref_LeaveApplications_Amounts AS RA (nolock) ON RA.ApplicationID = LAP.ID
				               INNER JOIN LeaveAllocateTypes AS LT (nolock) ON LT.ID=RA.LeaveTypeID	
					           WHERE  LAP.ddate >= CONVERT(DATETIME, @leaveyearstartdate , 102) 
					  AND LAP.ddate <= CONVERT(DATETIME, @leaveyearenddate , 102) 
					  AND LAP.Approved =1 AND LAP.Deleted=0
					 group by SchedulingPersonID
					) LT ON LT.SchedulingPersonID = UD_UserID
                    GROUP BY  UD_UserID, LA.StaffNumber
				 )IMD
				  where   AnnualRemaining != 0 or PHLRemaining != 0 or CompRemaining != 0 or AdditionalRemaining != 0
				  or ExceptionalRemaining != 0 or Under11TOILRemaining != 0 or Over12TOILRemaining!= 0 or CasualRemaining != 0 or LongServiceRemaining!= 0
				  or OtherRemaining != 0

END