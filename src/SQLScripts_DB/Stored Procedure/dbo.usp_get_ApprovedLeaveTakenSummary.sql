USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ApprovedLeaveTakenSummary]    Script Date: 03/09/2025 17:12:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_get_ApprovedLeaveTakenSummary]
	@teamid INT,
	@leaveyearstartdate DATE,
	@leaveyearenddate DATE

AS
BEGIN

	SET NOCOUNT ON;

	DECLARE @ColumnList Varchar(Max)='',
			@vSQL NVARCHAR(Max),
			@SelectColumnList Varchar(Max)='';

	SELECT @ColumnList= STUFF((SELECT ',[' + AllocName +']'
	from LeaveAllocateTypes (nolock)
	FOR XML PATH('')),1,1,'')

	SELECT @SelectColumnList= STUFF((SELECT ',SUM (LT.[' + AllocName +']) AS "' + AllocName+'"'
	from LeaveAllocateTypes (nolock)
	FOR XML PATH('')),1,1,'')

	SET @vSQL =   ' SELECT  
					LA.SchedulingPersonID, UD.UD_StaffNumber AS StaffNumber,'+
					@SelectColumnList+'
					FROM dbo.leaveapplications (nolock) LA
					INNER JOIN UserDetails UD (NOLOCK) ON UD.UD_UserID = LA.SchedulingPersonID
					INNER JOIN ( select distinct ScheduledPersonID 
								  from ScheduledPersonTeam_LINK (nolock) 
								  WHERE IsHomeTeam = 1 
									and TeamID = '+CAST(@teamid AS VARCHAR)+' 
									and ISNULL(EndDate,'''+FORMAT(@leaveyearstartdate,'yyyy-MM-dd')+''') >= '''+FORMAT(@leaveyearstartdate,'yyyy-MM-dd')
								   +''' and StartDate <= '''+FORMAT(@leaveyearenddate,'yyyy-MM-dd')
								+''') spl on spl.ScheduledPersonID = UD.UD_UserID 
					LEFT OUTER JOIN (
					SELECT AllocName, ApplicationID, amount, reasonid
					FROM LeaveAllocateTypes LT (nolock)
					LEFT JOIN ref_leaveapplications_amounts LP (nolock) ON LT.id = LP.LeaveTypeID
					) LA PIVOT ( MAX(Amount) FOR AllocName IN ('+@ColumnList+') ) LT
					ON LA.id = LT.applicationid
					WHERE ( LA.ddate >= '''+FORMAT(+@leaveyearstartdate,'yyyy-MM-dd')+''' ) 
					AND ( LA.ddate <= '''+FORMAT(@leaveyearenddate,'yyyy-MM-dd')+''')
					AND ( LA.deleted = 0 ) AND (LA.approved=1)
					GROUP BY UD.UD_StaffNumber,LA.SchedulingPersonID'


		EXEC ( @vSQL)

END